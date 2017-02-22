<?php

namespace ActionM\UnitPay\Test\Dummy;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'UNITPAY_orderSum',
        'UNITPAY_orderCurrency',
        'UNITPAY_orderStatus',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function SearchOrderFilterFails(Request $request, $order_id)
    {
        return false;
    }

    public static function SearchOrderFilterPaidforPayOrderFromGate(Request $request, $order_id, $orderStatus = 'paid', $orderSum = '1', $orderCurrency = '1')
    {
        $order = new self([
            'UNITPAY_orderSum' =>  $orderSum,
            'UNITPAY_orderCurrency' => $orderCurrency,
            'UNITPAY_orderStatus' => $orderStatus,
        ]);

        return $order;
    }

    public static function SearchOrderFilterPaid(Request $request, $order_id, $orderStatus = 'paid', $orderSum = '12345', $orderCurrency = 'RUB')
    {
        $order = new self([
            'UNITPAY_orderSum' =>  $orderSum,
            'UNITPAY_orderCurrency' => $orderCurrency,
            'UNITPAY_orderStatus' => $orderStatus,
        ]);

        return $order;
    }

    public static function SearchOrderFilterNotPaid(Request $request, $order_id, $orderStatus = 'no_paid', $orderSum = '', $orderCurrency = 'RUB')
    {
        $order = new self([
            'UNITPAY_orderSum' =>  $orderSum,
            'UNITPAY_orderCurrency' => $orderCurrency,
            'UNITPAY_orderStatus' => $orderStatus,
        ]);

        return $order;
    }

    public static function PaidOrderFilterFails(Request $request, $order)
    {
        return false;
    }

    public static function PaidOrderFilter(Request $request, $order)
    {
        return true;
    }
}
