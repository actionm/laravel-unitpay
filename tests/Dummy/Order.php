<?php

namespace ActionM\UnitPay\Test\Dummy;

use Illuminate\Http\Request;

class Order
{
    public static function SearchOrderFilterFails(Request $request, $order_id) {
        return false;
    }

    public static function SearchOrderFilterPaid(Request $request, $order_id) {
        return [
               'orderSum' => '12345',
               'orderCurrency' => 'RUB',
               'orderStatus' => 'paid',
        ];
    }

    public static function SearchOrderFilterNotPaid(Request $request, $order_id) {
        return [
            'orderSum' => '',
            'orderCurrency' => 'RUB',
            'orderStatus' => 'not_paid',
        ];
    }

    public static function PaidOrderFilterFails(Request $request, $order) {
        return false;
    }

    public static function PaidOrderFilter(Request $request, $order) {
        return true;
    }

}