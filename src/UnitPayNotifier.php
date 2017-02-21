<?php

namespace ActionM\UnitPay;

use ActionM\UnitPay\Events\UnitPayEvent;
use Illuminate\Contracts\Events\Dispatcher;
use ActionM\UnitPay\Exceptions\InvalidConfiguration;

class UnitPayNotifier
{
    /**
     * register Notifier.
     */
    public function subscribe(Dispatcher $events)
    {
        // Listen events and send notification
        $events->listen(UnitPayEvent::class, function ($event) {
            $event->type = str_replace('unitpay.', '', $event->type);

            if (! in_array($event->type, ['info', 'success', 'error'])) {
                $event->type = 'error';
            }

            $notifiable = app(config('unitpay.notifiable'));

            $notification = app(config('unitpay.notification'));
            $notification->setEvent($event);

            if (! $this->isValidNotificationClass($notification)) {
                throw InvalidConfiguration::notificationClassInvalid(get_class($notification));
            }

            if ($this->shouldSendNotification($notification)) {
                $notifiable->notify($notification);
            }
        });
    }

    public function isValidNotificationClass($notification)
    {
        if (get_class($notification) === UnitPayNotification::class) {
            return true;
        }

        if (is_subclass_of($notification, UnitPayNotification::class)) {
            return true;
        }

        return false;
    }

    public function shouldSendNotification($notification)
    {
        $callable = config('unitpay.notificationFilter');

        if (! is_callable($callable)) {
            return true;
        }

        return $callable($notification);
    }
}
