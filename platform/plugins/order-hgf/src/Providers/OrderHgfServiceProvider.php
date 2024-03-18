<?php

namespace Botble\OrderHgf\Providers;

use Botble\OrderHgf\Models\OrderHgf;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class OrderHgfServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/order-hgf')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'order-purchase']);

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(OrderHgf::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'order-hgf',
                'priority' => 8,
                'parent_id' => null,
                'name' => 'Đơn hàng HGF',
                'icon' => 'fa fa-list',
                'permissions' => 'hgf.index',
            ])
            ->registerItem([
                'id' => 'hgf-purchase-order',
                'priority' => 1,
                'parent_id' => 'order-hgf',
                'name' => 'Yêu cầu sản xuất',
                'icon' => 'fa fa-bolt',
                'url' => route('hgf.admin.purchase-order.index'),
                'permissions' => 'hgf.admin.purchase-order.index',
            ])
            ->registerItem([
                'id' => 'hgf-production-order',
                'priority' => 2,
                'parent_id' => 'order-hgf',
                'name' => 'Đơn đặt hàng',
                'icon' => 'fa-solid fa-receipt',
                'url' => route('hgf.admin.production.index'),
                'permissions' => 'hgf.admin.production.index',
            ]);
        });
    }
}
