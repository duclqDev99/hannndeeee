<?php

namespace Botble\OverviewReport\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class OverviewReportServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/overview-report')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions', 'overview-report'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-overview-report',
                'priority' => 2,
                'parent_id' => null,
                'name' => 'plugins/overview-report::overview-report.name',
                'icon' => 'ti ti-chart-donut-2',
                'url' => route('overview-report.index'),
                'permissions' => ['overview-report.index'],
            ]);
        });
    }
}
