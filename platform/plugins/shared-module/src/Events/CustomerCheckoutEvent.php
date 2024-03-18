<?php

namespace Botble\SharedModule\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerCheckoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $customerData;
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  array  $customerData
     * @return void
     */
    public function __construct($order,array $customerData)
    {
        $this->order = $order;
        $this->customerData = $customerData;
    }
}