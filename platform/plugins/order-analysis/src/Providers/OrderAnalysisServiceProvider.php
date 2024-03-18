<?php

namespace Botble\OrderAnalysis\Providers;

use Botble\OrderAnalysis\Models\OrderAnalysis;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class OrderAnalysisServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/order-analysis')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(OrderAnalysis::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-order-analysis',
                'priority' => 5,
                'parent_id' => null,
                'name' => 'Quản lý bản thiết kế',
                'icon' => 'fa fa-list',
            ])
                ->registerItem([
                    'id' => 'cms-plugins-order-analysis-index',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-order-analysis',
                    'name' => 'Quản lý bản thiết kế',
                    'icon' => 'fa fa-brands fa-atlassian',
                    'url' => route('analyses.index'),
                    'permissions' => ['analyses.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-order-analysis-order',
                    'priority' => 9,
                    'parent_id' => 'cms-plugins-order-analysis',
                    'name' => 'Quản lý đơn',
                    'icon' => 'fa fa-bolt',
                    'url' => route('analyses.index'),
                    'permissions' => ['analyses.index'],
                ])
                ;
        });
    }
}
