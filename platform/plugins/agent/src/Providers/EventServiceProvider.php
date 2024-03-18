<?php

namespace Botble\Agent\Providers;

use Botble\Base\Events\RenderingAdminWidgetEvent;
use Botble\Agent\Listeners\RegisterAgentWidget;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        RenderingAdminWidgetEvent::class => [
            RegisterAgentWidget::class,
        ],

    ];

    public function boot(): void
    {

    }
}
