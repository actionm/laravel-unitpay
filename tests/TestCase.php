<?php

namespace ActionM\UnitPay\Test;

use ActionM\UnitPay\UnitPay;
use ActionM\UnitPay\UnitPayServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class TestCase extends Orchestra
{
    protected $unitpay;

    public function setUp()
    {
        parent::setUp();
        $this->unitpay = $this->app['unitpay'];

        NotificationFacade::fake();

        $this->app['config']->set('unitpay.UNITPAY_PUBLIC_KEY', 'public_key');
        $this->app['config']->set('unitpay.UNITPAY_SECRET_KEY', 'secret_key');
    }

    protected function getPackageProviders($app)
    {
        return [
            UnitPayServiceProvider::class,
        ];
    }

    protected function withConfig(array $config)
    {
        $this->app['config']->set($config);
        $this->app->forgetInstance(UnitPay::class);
        $this->unitpay = $this->app->make(UnitPay::class);
    }
}
