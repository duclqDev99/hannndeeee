<?php

namespace Botble\SharedModule\Providers;

use Botble\SharedModule\Events\CustomerCheckoutEvent;
use Botble\SharedModule\Listeners\CreateCustomerCheckoutListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CustomerCheckoutEvent::class => [
            CreateCustomerCheckoutListener::class,
        ],
    ];
}
