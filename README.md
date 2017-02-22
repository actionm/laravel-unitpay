# Laravel payment processor package for UnitPay gateway

[![Latest Stable Version](https://poser.pugx.org/actionm/laravel-unitpay/v/stable)](https://packagist.org/packages/actionm/laravel-unitpay)
[![Build Status](https://img.shields.io/travis/actionm/laravel-unitpay/master.svg?style=flat-square)](https://travis-ci.org/actionm/laravel-unitpay)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/91033cd8-7b31-4001-8a18-331336c7dcd3/mini.png)](https://insight.sensiolabs.com/projects/91033cd8-7b31-4001-8a18-331336c7dcd3)
[![Quality Score](https://img.shields.io/scrutinizer/g/actionm/laravel-unitpay.svg?style=flat-square)](https://scrutinizer-ci.com/g/actionm/laravel-unitpay)
[![Total Downloads](https://img.shields.io/packagist/dt/actionm/laravel-unitpay.svg?style=flat-square)](https://packagist.org/packages/actionm/laravel-unitpay)
[![License](https://poser.pugx.org/actionm/laravel-unitpay/license)](https://packagist.org/packages/actionm/laravel-unitpay)

Accept payments via UnitPay ([unitpay.ru](http://unitpay.ru)) using this Laravel framework package ([Laravel](https://laravel.com)).

- receive payments, adding just the two callbacks
- receive payment notifications via your email or Slack

You can accept payments with Unitpay via Yandex.Money, QIWI, WebMoney, PayPal, credit cards etc.

#### Laravel 5.3, 5.4, PHP >= 5.6.4

## Installation

You can install the package through Composer:

``` bash
composer require actionm/laravel-unitpay
```


Add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [

    ActionM\UnitPay\UnitPayServiceProvider::class,
    
]
```

Add the `UnitPay` facade to your facades array:

```php
    'UnitPay' => ActionM\UnitPay\Facades\UnitPay::class,
```

Publish the configuration file and views
``` bash
php artisan vendor:publish --provider="ActionM\UnitPay\UnitPayServiceProvider" 
```

Publish only the configuration file
``` bash
php artisan vendor:publish --provider="ActionM\UnitPay\UnitPayServiceProvider" --tag=config 
```

Publish only the views
``` bash
php artisan vendor:publish --provider="ActionM\UnitPay\UnitPayServiceProvider" --tag=views 
```

## Configuration

Once you have published the configuration files, please edit the config file in `config/unitpay.php`.

- Create an account on [unitpay.ru](http://unitpay.ru)
- Add your project, copy the `PUBLIC KEY` and `SECRET KEY` params and paste into `config/unitpay.php`
- After the configuration has been published, edit `config/unitpay.php`
- Set the callback static function for `searchOrderFilter` and `paidOrderFilter`
- Set notification channels (email and/or Slack) and Slack `webhook_url` 
 
## Usage

1) Generate an HTML payment form with enabled payment methods:

``` php
$payment_amount = Order amount 

$payment_no = Unique order ID in your project 

$user_email = User email

$item_name = Name of your order item

$currency =  'RUB' or 'UAH','BYR','EUR','USD'
```

``` php
UnitPay::generatePaymentForm($payment_amount, $payment_no, $user_email, $item_name, $currency);
```

Customize the HTML payment form in the published view:
 
`app/resources/views/vendor/unitpay/payment_form.blade.php`

2) Process the request from UnitPay:
``` php
UnitPay::payOrderFromGate(Request $request)
```
## Important

You must define callbacks in `config/unitpay.php` to search the order and save the paid order.


``` php
 'searchOrderFilter' => null  // ExampleController:searchOrderFilter($request)
```

``` php
 'paidOrderFilter' => null  // ExampleController::paidOrderFilter($request,$order)
```

## Example

The process scheme:

1. The request comes from `unitpay.ru` `GET` `http://yourproject.com/unitpay/result` (with params).
2. The function`ExampleController@payOrderFromGate` runs the validation process (auto-validation request params).
3. The static function `searchOrderFilter` will be called (see `config/unitpay.php` `searchOrderFilter`) to search the order by the unique id.
4. If the current order status is NOT `paid` in your database, the static function `paidOrderFilter` will be called (see `config/unitpay.php` `paidOrderFilter`).

Add the route to `routes/web.php`:
``` php
 Route::get('/unitpay/result', 'ExampleController@payOrderFromGate');
```

> **Note:**
don't forget to save your full route url (e.g. http://example.com/unitpay/result ) for your project on [unitpay.ru](unitpay.ru).

Create the following controller: `/app/Http/Controllers/ExampleController.php`:

``` php
class ExampleController extends Controller
{

    /**
     * Search the order if the request from unitpay is received.
     * Return the order with required details for the unitpay request verification.
     *
     * @param Request $request
     * @param $order_id
     * @return mixed
     */
    public static function searchOrderFilter(Request $request, $order_id) {

        // If the order with the unique order ID exists in the database
        $order = Order::where('unique_id', $order_id)->first();

        if ($order) {
            $order['UNITPAY_orderSum'] = $order->amount; // from your database
            $order['UNITPAY_orderCurrency'] = 'RUB';  // from your database

            // if the current_order is already paid in your database, return strict "paid"; 
            // if not, return something else
            $order['UNITPAY_orderStatus'] = $order->order_status; // from your database
            return $order;
        }

        return false;
    }

    /**
     * When the payment of the order is received from unitpay, you can process the paid order.
     * !Important: don't forget to set the order status as "paid" in your database.
     *
     * @param Request $request
     * @param $order
     * @return bool
     */
    public static function paidOrderFilter(Request $request, $order)
    {
        // Your code should be here:
        YourOrderController::saveOrderAsPaid($order);

        // Return TRUE if the order is saved as "paid" in the database or FALSE if some error occurs.
        // If you return FALSE, then you can repeat the failed paid requests on the unitpay website manually.
        return true;
    }

    /**
     * Process the request from the UnitPay route.
     * searchOrderFilter is called to search the order.
     * If the order is paid for the first time, paidOrderFilter is called to set the order status.
     * If searchOrderFilter returns the "paid" order status, then paidOrderFilter will not be called.
     *
     * @param Request $request
     * @return mixed
     */
    public function payOrderFromGate(Request $request)
    {
        return UnitPay::payOrderFromGate($request);
    }
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please send me an email at actionmanager@gmail.com instead of using the issue tracker.

## Credits

- [ActionM](https://github.com/actionm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
