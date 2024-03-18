<?php

namespace Botble\ViettelPost\Providers;

use Botble\ViettelPost\Commands\InitViettelPostCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InitViettelPostCommand::class,
        ]);
    }
}
