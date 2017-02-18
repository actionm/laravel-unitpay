<?php

namespace ActionM\UnitPay\Events;

use Illuminate\Queue\SerializesModels;

class UnitPayEvent
{
    use SerializesModels;

    public $type;
    public $title;
    public $details;
    public $ip;

    /**
     * Create a new event instance.
     * @param $type
     * @param $details
     */
    public function __construct($type, $details)
    {
        $this->type = $type;
        $this->title = $details['title'];
        $this->details = print_r($details['request'], true);
        $this->ip = $details['ip'];
    }
}
