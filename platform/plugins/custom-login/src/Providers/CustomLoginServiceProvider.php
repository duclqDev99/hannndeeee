<?php

namespace Botble\CustomLogin\Providers;

use Botble\Base\Facades\Assets;
use Botble\CustomLogin\Models\CustomLogin;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Theme\Asset;
use Illuminate\Routing\Events\RouteMatched;

class CustomLoginServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/custom-login')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();


    }
}
