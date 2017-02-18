<?php

namespace ActionM\UnitPay\Exceptions;

use Exception;
use Illuminate\Notifications\Notification;

class InvalidConfiguration extends Exception
{
    public static function notificationClassInvalid($className): self
    {
        return new self("Class {$className} is an invalid notification class. ".
            'A notification class must extend '.Notification::class);
    }

    public static function searchOrderFilterInvalid(): self
    {
        return new self("UnitPay config: SearchOrderFilter callback not set");
    }

    public static function orderPaidFilterInvalid(): self
    {
        return new self("UnitPay config: PaidOrderFilter callback not set");
    }

    public static function generatePaymentFormOrderParamsNotSet($field): self
    {
        return new self("UnitPay config: generatePaymentForm required order params not set ( field: `".$field."`)");
    }
    public static function generatePaymentFormOrderInvalidCurrency($currency): self
    {
        return new self("UnitPay config: generatePaymentForm required order params not set ( field: `".$currency."`)");
    }
}
