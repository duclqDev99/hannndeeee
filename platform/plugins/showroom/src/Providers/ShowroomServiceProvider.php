<?php

namespace Botble\Showroom\Providers;

use Botble\Showroom\Models\Showroom;
use Botble\Agent\Models\Agent;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Showroom\Models\ShowroomUser;
use Botble\Showroom\Models\ShowroomWarehouse;
use Botble\Showroom\Repositories\Interfaces\UserShowRoomInterface;
use Botble\Agent\Repositories\Agents\Interfaces\AgentRepositoryInterface;
use Botble\Showroom\Repositories\Eloquent\UserShowRoomRepository;
use Botble\Agent\Repositories\Agents\Eloquent\AgentRepository;
use Botble\Ecommerce\Models\Customer;
use Botble\Showroom\Repositories\Report\Eloquent\ReportRepository;
use Botble\Showroom\Repositories\Report\Interfaces\ReportRepositoryInterfaces;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Arr;

class ShowroomServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        add_filter(PROCESS_CHECKOUT_RULES_REQUEST_ECOMMERCE, [$this, 'unsetRequirePasswordCreateCustomer'], 1, 2);

        $this
            ->setNamespace('plugins/showroom')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'report', 'order', 'customer', 'product', 'shipment']);
        $this->app->register(EventServiceProvider::class);
        $this->app->bind(AgentRepositoryInterface::class, function () {
            return new AgentRepository(new Agent());
        });

        // if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
        //     \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Showroom::class, [
        //         'name',
        //     ]);
        // }
        $this->app->bind(UserShowRoomInterface::class, function () {
            return new UserShowRoomRepository(new Showroom());
        });

        $this->app->bind(ReportRepositoryInterfaces::class, function () {
            return new ReportRepository(new Showroom());
        });




        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-showroom',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'plugins/showroom::showroom.menu_name.showroom',
                'icon' => 'ti ti-vip',
                'url' => route('showroom.index'),
                'permissions' => ['showroom.management', 'showroom.index', 'showroom.all'],
            ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-list-showroom',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.list_showroom',
                    'icon' => 'ti ti-menu',
                    'url' => route('showroom.index'),
                    'permissions' => ['showroom.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-list-warehouse',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.list_warehouse',
                    'icon' => 'ti ti-archive',
                    'url' => route('showroom-warehouse.index'),
                    'permissions' => ['showroom-warehouse.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-list-product',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.list_product',
                    'icon' => 'ti ti-app-window-filled',
                    'url' => route('showroom-product.index'),
                    'permissions' => ['showroom.management'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-report',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.report',
                    'icon' => 'ti ti-chart-donut-2',
                    'url' => route('showroom.report.index'),
                    'permissions' => ['showroom.report.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-shipment',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.shipment',
                    'icon' => 'ti ti-truck-loading',
                    'url' => route('showroom.shipments.index'),
                    'permissions' => ['showroom.orders.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-order',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'plugins/showroom::showroom.menu_name.order',
                    'icon' => 'ti ti-notebook',
                    'url' => route('showroom.orders.index'),
                    'permissions' => ['showroom.orders.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-proposal-showroom-receipt',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'Đề xuất nhập kho',
                    'icon' => 'ti ti-download',
                    'url' => route('proposal-showroom-receipt.index'),
                    'permissions' => ['proposal-showroom-receipt.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-proposal-issue',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'Yêu cầu trả hàng',
                    'icon' => 'ti ti-upload',
                    'url' => route('showroom-proposal-issue.index'),
                    'permissions' => ['showroom-proposal-issue.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-receipt',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'Phiếu nhập kho',
                    'icon' => 'ti ti-clipboard-plus',
                    'url' => route('showroom-receipt.index'),
                    'permissions' => ['showroom-receipt.index', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-issue',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'Phiếu xuất kho',
                    'icon' => 'ti ti-clipboard-check',
                    'url' => route('showroom-issue.index'),
                    'permissions' => ['showroom-issue.index', 'showroom.all'],
                ])
                //->
                // registerItem([
                //     'id' => 'cms-plugins-showroom-receipt',
                //     'priority' => 5,
                //     'parent_id' => 'cms-plugins-showroom',
                //     'name' => 'plugins/showroom::showroom.menu_name.receipt_warehouse',
                //     'icon' => 'ti ti-clipboard-plus',
                //     'url' => route('showroom-receipt.index'),
                //     'permissions' => ['showroom-receipt.index'],
                // ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-qr',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-showroom',
                    'name' => 'Quét QR',
                    'icon' => 'fa fa-qrcode',
                    'url' => route('showroom-qr.check-qr'),
                    'permissions' => ['showroom-qr.check-qr', 'showroom.all'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-showroom-exchange-goods',
                    'priority' => 7,
                    'name' => 'plugins/showroom::showroom.exchange_goods.name',
                    'parent_id' => 'cms-plugins-showroom',
                    'icon' => 'fa fa-circle',
                    'url' => route('exchange-goods.index'),
                    'permissions' => ['exchange-goods.index'],
                ]);
        });
    }

    public function unsetRequirePasswordCreateCustomer($rules)
    {
        if (Arr::has($rules, 'password')) {
            Arr::forget($rules, 'password');
        }

        if (Arr::has($rules, 'password_confirmation')) {
            Arr::forget($rules, 'password_confirmation');
        };
        return $rules;
    }
}
