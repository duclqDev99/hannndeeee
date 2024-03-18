<?php

namespace Botble\Showroom\Providers;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Payment\Events\RenderingPaymentMethods;
use Botble\Showroom\Listeners\RegisterShowroomWidget;
use Botble\Showroom\Listeners\RemoveCodPaymentMethod;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        RenderingAdminWidgetEvent::class => [
            RegisterShowroomWidget::class,
        ],

        RenderingPaymentMethods::class => [
            RemoveCodPaymentMethod::class,
        ],
    ];

    public function boot(): void
    {

    }
}
