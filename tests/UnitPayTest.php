<?php

namespace ActionM\UnitPay\Test;

use Illuminate\Http\Request;
use ActionM\UnitPay\Test\Dummy\Order;
use ActionM\UnitPay\UnitPayNotifiable;
use ActionM\UnitPay\Events\UnitPayEvent;
use ActionM\UnitPay\UnitPayNotification;
use ActionM\UnitPay\Test\Dummy\AnotherNotifiable;
use ActionM\UnitPay\Test\Dummy\AnotherNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class UnitPayTest extends TestCase
{
    /** @test */
    public function test_env()
    {
        $this->assertEquals('testing', $this->app['env']);
    }

    /**
     * Send event with event_type.
     * @param $event_type
     * @return array|null
     */
    protected function fireEvent($event_type)
    {
        return event(
            new UnitPayEvent(
                $event_type, ['title' => 'UnitPay: notification', 'ip' => '127.0.0.1', 'request' => ['test' => 'test']]
            )
        );
    }

    /**
     * Create test request with custom method and add signature.
     * @param string $method
     * @param bool $signature
     * @return Request
     */
    protected function create_test_request($method = '', $signature = false)
    {
        $params = [
            'method' => $method,
            'params' => [
                'account' => '1',
                'date' => '1',
                'payerSum' => '1',
                'payerCurrency' => '1',
                'orderSum' => '1',
                'orderCurrency' => '1',
                'unitpayId' => '1',
            ],
        ];

        if ($signature === false) {
            $params['params']['signature'] = $this->unitpay->getSignature($method, $params['params'], $this->app['config']->get('unitpay.UNITPAY_SECRET_KEY'));
        } else {
            $params['params']['signature'] = $signature;
        }

        $request = new Request($params);

        return $request;
    }

    /* always public for callback test */
    public function returnsFalseWhenTypeIsEmpty($notification)
    {
        return false;
    }

    /* always public for callback test */
    public function returnsTrueWhenTypeIsNotEmpty($notification)
    {
        $type = $notification->getEvent()->type;

        return ! empty($type);
    }

    /** @test */
    public function it_can_send_notification_when_payment_error()
    {
        $this->fireEvent('unitpay.error');
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function it_can_send_notification_when_payment_success()
    {
        $this->fireEvent('unitpay.success');
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function it_can_send_notification_when_job_failed_to_different_notifiable()
    {
        $this->app['config']->set('unitpay.notifiable', AnotherNotifiable::class);
        $this->fireEvent('unitpay.success');
        NotificationFacade::assertSentTo(new AnotherNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function it_can_send_notification_when_job_failed_to_different_notification()
    {
        $this->app['config']->set('unitpay.notification', AnotherNotification::class);
        $this->fireEvent('unitpay.success');
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), AnotherNotification::class);
    }

    /** @test */
    public function it_filters_out_notifications_when_the_notificationFilter_returns_true()
    {
        $this->app['config']->set('unitpay.notificationFilter', [$this, 'returnsTrueWhenTypeIsEmpty']);
        $this->fireEvent('unitpay.success');
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function it_filters_out_notifications_when_the_notificationFilter_returns_false()
    {
        $this->app['config']->set('unitpay.notificationFilter', [$this, 'returnsFalseWhenTypeIsEmpty']);
        $this->fireEvent('unitpay.success');
        NotificationFacade::assertNotSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function check_if_allow_remote_ip()
    {
        $this->assertTrue(
            $this->unitpay->allowIP('127.0.0.1')
        );

        $this->assertFalse(
            $this->unitpay->allowIP('0.0.0.0')
        );
    }

    /** @test */
    public function compare_form_signature()
    {
        $this->assertEquals(
            'da5ef2f0c2f69e99414cabdba2de9b1f8a5e233bcb91f600d6581d8b6460cca5',
            $this->unitpay->getFormSignature('account', 'RUB', 'desc', 'sum', 'secretkey')
        );
    }

    /** @test */
    public function compare_request_signature()
    {
        $params['account'] = '2222222e-2222-3333-3333-cd51f1605a02';
        $params['date'] = '2020-02-02 22:21:16';
        $params['ip'] = '123.123.123.123';
        $params['operator'] = 'yandex';
        $params['orderCurrency'] = 'RUB';
        $params['orderSum'] = '777.00';
        $params['payerCurrency'] = 'RUB';
        $params['payerSum'] = '777.00';
        $params['paymentType'] = 'yandex';
        $params['profit'] = '777.00';
        $params['projectId'] = '12345';
        $params['sum'] = '499';
        $params['test'] = '0';
        $params['unitpayId'] = '12345';

        $this->assertEquals(
            'a1e5f350f3c18386c0780a1ad7ae71b7b9beb0d657cc3544c33c6adda744312b',
            $this->unitpay->getSignature('pay', $params, 'secretkey')
        );
    }

    /** @test */
    public function generate_order_validation_true()
    {
        $this->assertArrayHasKey('CURRENCY', $this->unitpay->generateUnitPayOrderWithRequiredFields('999', '12345', 'test@example.com', 'Item name', 'RUB'));
    }

    /** @test */
    public function generate_order_true_validation_false()
    {
        $this->expectException('ActionM\UnitPay\Exceptions\InvalidConfiguration');
        $this->unitpay->generateUnitPayOrderWithRequiredFields('', '', 'test@example.com', 'Item name', '');
    }

    /** @test */
    public function generate_payment_form()
    {
        $this->assertNotNull($this->unitpay->generatePaymentForm('999', '12345', 'test@example.com', 'Item name', 'RUB'));
        $this->assertEquals('unitpay::payment_form', $this->unitpay->generatePaymentForm('999', '12345', 'test@example.com', 'Item name', 'RUB')->getName());
    }

    /** @test */
    public function pay_order_form_validate_request()
    {
        $request = $this->create_test_request('check');
        $this->assertTrue($this->unitpay->validate($request));

        $request = $this->create_test_request('pay');
        $this->assertTrue($this->unitpay->validate($request));

        $request = $this->create_test_request('error');
        $this->assertTrue($this->unitpay->validate($request));

        $request = $this->create_test_request('unknown');
        $this->assertFalse($this->unitpay->validate($request));
    }

    /** @test */
    public function validate_signature()
    {
        $request = $this->create_test_request('check', '3c34ad7ce9bb9fc56e8621e0a7797f3377136f365bcac07c4222575802d02b6d');
        $this->assertTrue($this->unitpay->validate($request));
        $this->assertTrue($this->unitpay->validateSignature($request));

        $request = $this->create_test_request('check', 'invalid_signature');
        $this->assertTrue($this->unitpay->validate($request));
        $this->assertFalse($this->unitpay->validateSignature($request));
    }

    /** @test */
    public function test_order_need_callbacks()
    {
        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $this->expectException('ActionM\UnitPay\Exceptions\InvalidConfiguration');
        $this->unitpay->callFilterSearchOrder($request);

        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $this->expectException('ActionM\UnitPay\Exceptions\InvalidConfiguration');
        $this->unitpay->callFilterPaidOrder($request, ['order_id' => '12345']);
    }

    /** @test */
    public function search_order_has_callbacks_fails_and_notify()
    {
        $this->app['config']->set('unitpay.searchOrderFilter', [Order::class, 'SearchOrderFilterFails']);
        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $this->assertFalse($this->unitpay->callFilterSearchOrder($request));
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function validate_search_order_required_attributes_not_set()
    {
        $request = new Request([
            'params' => [
                'orderStatus' => 'paid',
                'orderSum' => '0',
                'orderCurrency' => 'USD',
            ],
        ]);

        $this->assertFalse($this->unitpay->validateSearchOrderRequiredAttributes($request, new Order()));
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function validate_search_order_required_attributes_true()
    {
        $request = new Request([
            'params' => [
                'orderStatus' => 'paid',
                'orderSum' => '999',
                'orderCurrency' => 'RUB',
            ],
        ]);

        $order = new Order([
            'UNITPAY_orderSum' =>  '999',
            'UNITPAY_orderCurrency' => 'RUB',
            'UNITPAY_orderStatus' => 'paid',
        ]);

        $this->assertTrue($this->unitpay->validateSearchOrderRequiredAttributes($request, $order));
    }

    /** @test */
    public function validate_search_order_required_attributes_compare_sum_and_currency_false()
    {
        $request = new Request([
            'params' => [
                'orderStatus' => 'paid',
                'orderSum' => '0',
                'orderCurrency' => 'USD',
            ],
        ]);

        $order = new Order([
            'UNITPAY_orderSum' =>  '999',
            'UNITPAY_orderCurrency' => 'RUB',
            'UNITPAY_orderStatus' => 'paid',
        ]);

        $this->assertFalse($this->unitpay->validateSearchOrderRequiredAttributes($request, $order));
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function paid_order_has_callbacks()
    {
        $this->app['config']->set('unitpay.searchOrderFilter', [Order::class, 'SearchOrderFilterPaid']);
        $this->app['config']->set('unitpay.paidOrderFilter', [Order::class, 'PaidOrderFilter']);
        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $this->assertTrue($this->unitpay->callFilterPaidOrder($request, ['order_id' => '12345']));
    }

    /** @test */
    public function paid_order_has_callbacks_fails()
    {
        $this->app['config']->set('unitpay.paidOrderFilter', [Order::class, 'PaidOrderFilterFails']);
        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $this->assertFalse($this->unitpay->callFilterPaidOrder($request, ['order_id' => '12345']));
    }

    /** @test */
    public function payOrderFromGate_SearchOrderFilter_fails()
    {
        $this->app['config']->set('unitpay.searchOrderFilter', [Order::class, 'SearchOrderFilterFails']);
        $request = $this->create_test_request('check', 'ec61edc55b99b7b62d8157dffd88895d72250e02163b1a60cd5f52d48d8a7015');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->assertArrayHasKey('error', $this->unitpay->payOrderFromGate($request));
    }

    /** @test */
    public function payOrderFromGate_method_check_SearchOrderFilterPaid()
    {
        $this->app['config']->set('unitpay.searchOrderFilter', [Order::class, 'SearchOrderFilterPaidforPayOrderFromGate']);
        $request = $this->create_test_request('check');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->assertArrayHasKey('result', $this->unitpay->payOrderFromGate($request));
        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }

    /** @test */
    public function payOrderFromGate_method_pay_SearchOrderFilterPaid()
    {
        $this->app['config']->set('unitpay.searchOrderFilter', [Order::class, 'SearchOrderFilterPaidforPayOrderFromGate']);
        $this->app['config']->set('unitpay.paidOrderFilter', [Order::class, 'PaidOrderFilter']);
        $request = $this->create_test_request('pay');

        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $this->assertArrayHasKey('result', $this->unitpay->payOrderFromGate($request));

        NotificationFacade::assertSentTo(new UnitPayNotifiable(), UnitPayNotification::class);
    }
}
