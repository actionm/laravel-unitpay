<?php

namespace ActionM\UnitPay\Exceptions;

use Exception;
use Illuminate\Notifications\Notification;

class InvalidConfiguration extends Exception
{
    public static function notificationClassInvalid($className)
    {
        return new self("Class {$className} is an invalid notification class. ".
            'A notification class must extend '.Notification::class);
    }

    public static function searchOrderFilterInvalid()
    {
        return new self('UnitPay config: searchOrderFilter callback not set');
    }

    public static function orderPaidFilterInvalid()
    {
        return new self('UnitPay config: paidOrderFilter callback not set');
    }

    public static function generatePaymentFormOrderParamsNotSet($field)
    {
        return new self('UnitPay config: generatePaymentForm required order params not set ( field: `'.$field.'`)');
    }

    public static function generatePaymentFormOrderInvalidCurrency($currency)
    {
        return new self('UnitPay config: generatePaymentForm required order params not set ( field: `'.$currency.'`)');
    }
}
