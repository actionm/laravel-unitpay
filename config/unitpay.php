<?php

return [

    /*
     * unitpay.ru PUBLIC KEY for project
     */
    'UNITPAY_PUBLIC_KEY' => env('UNITPAY_PUBLIC_KEY', ''),

    /*
     * unitpay.ru SECRET KEY for project
     */
    'UNITPAY_SECRET_KEY' => env('UNITPAY_SECRET_KEY', ''),

    /*
     * locale for payment form
     */
    'locale' => 'ru',  // ru || en

    /*
     * Hide other payment methods
     */
    'hideOtherMethods' => 'false',

    /*
     *  SearchOrderFilter
     *  Search order in the database and return order details
     *  Must return array with:
     *
     *  orderStatus
     *  orderCurrency
     *  orderSum
     */
    'searchOrderFilter' => null, //  'App\Http\Controllers\ExampleController::searchOrderFilter',

    /*
     *  PaidOrderFilter
     *  If current orderStatus from DB != paid then call PaidOrderFilter
     *  update order into DB & other actions
     */
    'paidOrderFilter' => null, //  'App\Http\Controllers\ExampleController::paidOrderFilter',

    'payment_forms' => [
        'cards' => true,
        'yandex' => true,
        'qiwi' => true,
        'cash' => true,
        'webmoney' => true,
    ],

    // Allowed ip's http://help.unitpay.ru/article/67-ip-addresses
    'allowed_ips' => [
        '31.186.100.49',
        '178.132.203.105',
        '52.29.152.23',
        '52.19.56.234',
    ],

    /*
     * The notification that will be send when payment request received.
     */
    'notification' => \ActionM\UnitPay\UnitPayNotification::class,

    /*
     * The notifiable to which the notification will be sent. The default
     * notifiable will use the mail and slack configuration specified
     * in this config file.
     */
    'notifiable' => \ActionM\UnitPay\UnitPayNotifiable::class,

    /*
     * By default notifications are sent always. You can pass a callable to filter
     * out certain notifications. The given callable will receive the notification. If the callable
     * return false, the notification will not be sent.
     */
    'notificationFilter' => null,

    /*
     * The channels to which the notification will be sent.
     */
    'channels' => ['mail', 'slack'],

    'mail' => [
        'to' => '',  // your email
    ],

    'slack' => [
        'webhook_url' => '', // slack web hook to send notifications
    ],
];
