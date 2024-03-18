<?php

namespace Botble\OrderRetail\Providers;

use Botble\OrderRetail\Models\OrderRetail;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Sales\Models\Order;
use Illuminate\Routing\Events\RouteMatched;

class OrderRetailServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/order-retail')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'order-step', 'order-purchase']);

        // if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
        //     \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Order::class, [
        //         'name',
        //     ]);
        // }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'order-retail',
                'priority' => 7,
                'parent_id' => null,
                'name' => 'Đơn hàng Retail',
                'icon' => 'fa fa-list',
                'permissions' => ['retail.index'],
            ])
            ->registerItem([
                'id' => 'retail-view-order-step',
                'priority' => 1,
                'parent_id' => 'order-retail',
                'name' => 'Tiến độ đơn hàng',
                'icon' => 'fa-solid fa-bars-progress',
                'url' => route('order-step.index'),
                'permissions' => 'retail.view-progress',
            ])  
            ->registerItem([
                'id' => 'cms-plugins-orders',
                'priority' => 1,
                'parent_id' => 'order-retail',
                'name' => 'Yêu cầu sản xuất',
                'icon' => 'fa fa-bolt',
                'url' => route('retail.sale.purchase-order.index'),
                'permissions' => 'retail.sale.purchase-order.index',
            ])
            ->registerItem([
                'id' => 'cms-plugins-order-quotation',
                'priority' => 2,
                'parent_id' => 'order-retail',
                'name' => 'Báo giá',
                'icon' => 'fa-solid fa-receipt',
                'url' => route('retail.sale.quotation.index'),
                'permissions' => 'retail.sale.quotation.index',
            ])
            ->registerItem([
                'id' => 'accountant-quotation',
                'priority' => 3,
                'parent_id' => 'order-retail',
                'name' => 'Kế toán kiểm duyệt',
                'icon' => 'fa-solid fa-receipt',
                'url' => route('retail.accountant.quotation.index'),
                'permissions' => 'retail.accountant.index',
            ])
            ->registerItem([
                'id' => 'cms-plugins-order-production',
                'priority' => 4,
                'parent_id' => 'order-retail',
                'name' => 'Đơn đặt hàng',
                'icon' => 'fa-solid fa-receipt',
                'url' => route('retail.sale.production.index'),
                'permissions' => 'retail.sale.production.index',
            ]);
        });
    }
}
