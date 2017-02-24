<?php

namespace ActionM\UnitPay;

use Illuminate\Support\ServiceProvider;

class UnitPayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/unitpay.php' => config_path('unitpay.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/unitpay'),
        ], 'views');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'unitpay');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/unitpay.php', 'unitpay');

        $this->app['events']->subscribe(UnitPayNotifier::class);

        $this->app->singleton('unitpay', function () {
            return $this->app->make('ActionM\UnitPay\UnitPay');
        });

        $this->app->alias('unitpay', 'UnitPay');

        $this->app->singleton(UnitPayNotifier::class);
    }
}
