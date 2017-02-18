<?php

namespace ActionM\UnitPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Actionm\UnitPay\UnitPayController
 */
class UnitPay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'unitpay';
    }
}
