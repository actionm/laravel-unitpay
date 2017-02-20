<?php

namespace ActionM\UnitPay;

use ActionM\UnitPay\Events\UnitPayEvent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Notification as IlluminateNotification;

class Notification extends IlluminateNotification
{
    /** @var \ActionM\UnitPay\Events\UnitPayEvent * */
    protected $event;

    public function via($notifiable)
    {
        return config('unitpay.channels');
    }

    public function setEvent(UnitPayEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject('UnitPay payment message from '.config('app.url'))
            ->line($this->event->title)
            ->line('IP: '.$this->event->ip)
            ->line("Request details: {$this->event->details}");
    }

    public function toSlack()
    {
        $slack_message = new SlackMessage();
        $slack_message->level = $this->event->type;

        return $slack_message
            ->content('UnitPay payment message from '.config('app.url'))
            ->attachment(function(SlackAttachment $attachment) {
                $attachment->fields([
                    'Title' => $this->event->title,
                    'IP' => $this->event->ip,
                    'Request details' => $this->event->details,
                ]);
            });
    }
}
