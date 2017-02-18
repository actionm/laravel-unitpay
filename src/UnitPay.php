<?php

namespace ActionM\UnitPay;

use Illuminate\Http\Request;
use ActionM\UnitPay\Events\UnitPayEvent;
use Illuminate\Support\Facades\Validator;
use ActionM\UnitPay\Exceptions\InvalidConfiguration;

class UnitPay
{
    public function __construct()
    {
    }

    /**
     * Allow if ip address is in whitelist.
     * @param $ip
     * @return bool
     */
    public function allowIP($ip)
    {
        // Allow local ip
        if ($ip == '127.0.0.1') {
            return true;
        }

        return in_array($ip, config('unitpay.allowed_ips'));
    }

    /**
     * Return json error result.
     * @param $message
     * @return mixed
     */
    public function ResponseError($message)
    {
        $result['error']['message'] = $message;

        return $result;
    }

    /**
     * Return json success result.
     * @param $message
     * @return mixed
     */
    public function ResponseOK($message)
    {
        $result['result']['message'] = $message;

        return $result;
    }

    /**
     * Fill event details to pass title and request params as array.
     * @param $event_type
     * @param $event_title
     * @param Request $request
     */
    public function eventFillAndSend($event_type, $event_title, Request $request)
    {
        $event_details = [
            'title' => 'UnitPay: '.$event_title,
            'ip' => $request->ip(),
            'request' => $request->all(),
        ];

        event(
            new UnitPayEvent($event_type, $event_details)
        );
    }

    /**
     * Return hash for order form params.
     * @param $account
     * @param $currency
     * @param $desc
     * @param $sum
     * @param $secretKey
     * @return string
     */
    public function getFormSignature($account, $currency, $desc, $sum, $secretKey)
    {
        $hashStr = $account.'{up}'.$currency.'{up}'.$desc.'{up}'.$sum.'{up}'.$secretKey;

        return hash('sha256', $hashStr);
    }

    /**
     * Return hash for params from UnitPay gate.
     * @param $method
     * @param array $params
     * @param $secretKey
     * @return string
     */
    public function getSignature($method, array $params, $secretKey)
    {
        ksort($params);
        unset($params['sign'], $params['signature']);
        array_push($params, $secretKey);
        array_unshift($params, $method);

        return hash('sha256', implode('{up}', $params));
    }

    /**
     * Generate UnitPay order array with required array for order form.
     * @param $payment_amount
     * @param $payment_no
     * @param $user_email
     * @param $item_name
     * @param $currency
     * @return array
     */
    public function generateUnitPayOrderWithRequiredFields($payment_amount, $payment_no, $user_email, $item_name, $currency)
    {
        $order = [
            'PAYMENT_AMOUNT' => $payment_amount,
            'PAYMENT_NO' => $payment_no,
            'USER_EMAIL' => $user_email,
            'ITEM_NAME' => $item_name,
            'CURRENCY' => $currency,
        ];

        $this->requiredOrderParamsCheck($order);

        return $order;
    }

    /**
     * Check required order params for order form and raise an exception if fails.
     * @param $order
     * @throws InvalidConfiguration
     */
    public function requiredOrderParamsCheck($order)
    {
        $required_fields = [
            'PAYMENT_AMOUNT',
            'PAYMENT_NO',
            'USER_EMAIL',
            'ITEM_NAME',
            'CURRENCY',
        ];

        foreach ($required_fields as $key => $value) {
            if (! array_key_exists($value, $order) || empty($order[$value])) {
                throw InvalidConfiguration::generatePaymentFormOrderParamsNotSet($value);
            }
        }

        $currency_arr = [
            'RUB',
            'UAH',
            'BYR',
            'EUR',
            'USD',
        ];

        if (! in_array($order['CURRENCY'], $currency_arr)) {
            throw InvalidConfiguration::generatePaymentFormOrderInvalidCurrency($order['CURRENCY']);
        }
    }

    /**
     * Generate html forms from view with payment buttons
     * Note: you can customise the view via artisan:publish.
     * @param $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function generatePaymentForm($payment_amount, $payment_no, $user_email, $item_name, $currency)
    {
        $order = $this->generateUnitPayOrderWithRequiredFields($payment_amount, $payment_no, $user_email, $item_name, $currency);

        $this->requiredOrderParamsCheck($order);

        $payment_fields['LOCALE'] = config('unitpay.locale', 'ru');
        $payment_fields['PUB_KEY'] = config('unitpay.UNITPAY_PUBLIC_KEY');
        $payment_fields['PAYMENT_AMOUNT'] = $order['PAYMENT_AMOUNT'];
        $payment_fields['PAYMENT_NO'] = $order['PAYMENT_NO'];
        $payment_fields['USER_EMAIL'] = $order['USER_EMAIL'];
        $payment_fields['ITEM_NAME'] = $order['ITEM_NAME'];
        $payment_fields['CURRENCY'] = $order['CURRENCY'];

        $payment_fields['SIGN'] = $this->getFormSignature(
            $payment_fields['PAYMENT_NO'],
            $payment_fields['CURRENCY'],
            $payment_fields['ITEM_NAME'],
            $payment_fields['PAYMENT_AMOUNT'],
            config('unitpay.UNITPAY_SECRET_KEY')
        );

        return view('unitpay::payment_form', compact('payment_fields'));
    }

    /**
     * Validate request params from UnitPay gate.
     * @param Request $request
     * @return bool
     */
    public function validate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:check,pay,error',
            'params.account' => 'required',
            'params.date' => 'required',
            'params.payerSum' => 'required',
            'params.payerCurrency' => 'required',
            'params.signature' => 'required',
            'params.orderSum' => 'required',
            'params.unitpayId' => 'required',
        ]);

        if ($validator->fails()) {
            return false;
        }

        return true;
    }

    /**
     * Validate request signature from UnitPay gate.
     * @param Request $request
     * @return bool
     */
    public function validateSignature(Request $request)
    {
        $sign = $this->getSignature($request->get('method'), $request->get('params'), config('unitpay.UNITPAY_SECRET_KEY'));

        if ($request->input('params.signature') != $sign) {
            return false;
        }

        return true;
    }

    /**
     * Validate ip, request params and signature from UnitPay gate.
     * @param Request $request
     * @return bool
     */
    public function validateOrderRequestFromGate(Request $request)
    {
        if (! $this->AllowIP($request->ip()) || ! $this->validate($request) || ! $this->validateSignature($request)) {
            $this->eventFillAndSend('unitpay.error', 'validateOrderRequestFromGate', $request);

            return false;
        }

        return true;
    }

    /**
     * Call SearchOrderFilter and check return order params.
     * @param Request $request
     * @return bool
     * @throws InvalidConfiguration
     */
    public function SearchOrderFilterCall(Request $request)
    {
        $callable = config('unitpay.SearchOrderFilter');

        if (! is_callable($callable)) {
            throw InvalidConfiguration::searchOrderFilterInvalid();
        }

        /*
         *  SearchOrderFilter
         *  Search order in the database and return order details
         *  Must return array with:
         *
         *  orderStatus
         *  orderCurrency
         *  orderSum
         */

        $order = $callable($request, $request->input('params.account'));

        if (! $order) {
            $this->eventFillAndSend('unitpay.error', 'orderNotFound', $request);

            return false;
        }

        if (! array_key_exists('orderStatus', $order)) {
            $this->eventFillAndSend('unitpay.error', 'orderStatusInvalid', $request);

            return false;
        }

        if (! array_key_exists('orderSum', $order) && $request->input('params.orderSum') != $order['orderSum']) {
            $this->eventFillAndSend('unitpay.error', 'orderSumInvalid', $request);

            return false;
        }

        if (! array_key_exists('orderCurrency', $order) && $request->input('params.orderCurrency') != $order['orderCurrency']) {
            $this->eventFillAndSend('unitpay.error', 'orderCurrencyInvalid', $request);

            return false;
        }

        return $order;
    }

    /**
     * Call PaidOrderFilter if order not paid.
     * @param Request $request
     * @param $order
     * @return mixed
     * @throws InvalidConfiguration
     */
    public function PaidOrderFilterCall(Request $request, $order)
    {
        $callable = config('unitpay.PaidOrderFilter');

        if (! is_callable($callable)) {
            throw InvalidConfiguration::orderPaidFilterInvalid();
        }

        // Run PaidOrderFilter callback
        return $callable($request, $order);
    }

    /**
     * Run UnitPay::payOrderFromGate($request) when receive request from UnitPay gate.
     * @param Request $request
     * @return bool
     */
    public function payOrderFromGate(Request $request)
    {
        // Validate request params from UnitPay server.
        if (! $this->validateOrderRequestFromGate($request)) {
            return $this->ResponseError('validateOrderRequestFromGate');
        }

        // Search and return order
        $order = $this->SearchOrderFilterCall($request);

        if (! $order) {
            return $this->ResponseError('searchOrderFilter');
        }

        // Return success response for check and error methods
        if (in_array($request->get('method'), ['check', 'error'])) {
            $this->eventFillAndSend('unitpay.info', 'payOrderFromGate method = '.$request->get('method'), $request);

            return $this->ResponseOK('OK');
        }

        // If method unknown then return error
        if ($request->get('method') != 'pay') {
            return $this->ResponseError('Invalid request');
        }

        // If method pay and current order status is paid
        // return success response and notify info
        if (mb_strtolower($order['orderStatus']) === 'paid') {
            $this->eventFillAndSend('unitpay.info', 'order already paid', $request);

            return $this->ResponseOK('OK');
        }

        // Current order is paid in UnitPay and not paid in database

        $this->eventFillAndSend('unitpay.success', 'paid order', $request);

        // PaidOrderFilter - update order into DB as paid & other actions
        // if return false then error
        if (! $this->PaidOrderFilterCall($request, $order)) {
            $this->eventFillAndSend('unitpay.error', 'PaidOrderFilterCall', $request);

            return $this->ResponseError('PaidOrderFilterCall');
        }

        // Order is paid in UnitPay and updated in database
        return $this->ResponseOK('OK');
    }
}
