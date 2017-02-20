<?php

namespace ActionM\UnitPay;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use ActionM\UnitPay\Exceptions\InvalidConfiguration;

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

        $this->testingEnv();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/unitpay.php', 'unitpay');

        $this->app['events']->subscribe(UnitPayNotifier::class);

        $this->app->singleton('unitpay', function() {
            return $this->app->make('ActionM\UnitPay\UnitPay');
        });

        $this->app->alias('unitpay', 'UnitPay');

        $this->app->singleton(UnitPayNotifier::class);
    }

    /**
     * Not check config if testing env.
     * @throws InvalidConfiguration
     */
    public function testingEnv()
    {
        if (!App::environment('testing')) {
            $callable = config('unitpay.searchOrderFilter');

            if (!is_callable($callable)) {
                throw InvalidConfiguration::searchOrderFilterInvalid();
            }

            $callable = config('unitpay.paidOrderFilter');

            if (!is_callable($callable)) {
                throw InvalidConfiguration::orderPaidFilterInvalid();
            }
        }
    }
}
