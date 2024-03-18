<?php

namespace Botble\SharedModule\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\SharedModule\Commands\ReportDailyCommand;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            ReportDailyCommand::class,
        ]);
    }
}
