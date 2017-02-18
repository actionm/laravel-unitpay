<?php

namespace ActionM\UnitPay;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail()
    {
        return config('unitpay.mail.to');
    }

    public function routeNotificationForSlack()
    {
        return config('unitpay.slack.webhook_url');
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return 1;
    }
}
