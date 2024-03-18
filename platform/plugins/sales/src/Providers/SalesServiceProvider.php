<?php

namespace Botble\Sales\Providers;

use Botble\Sales\Models\Sales;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Supports\TwigExtension;
use Botble\Sales\Models\Order;
use Botble\Sales\Observers\OrderObserver;
use Illuminate\Routing\Events\RouteMatched;

class SalesServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        // Model Order is observed by OrderObserver 
        Order::observe(OrderObserver::class);
      
        $this
            ->setNamespace('plugins/sales')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web','api']);    

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Sales::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
           
        });
    }
}
