<?php

namespace Botble\HubWarehouse\Providers;

use Botble\ACL\Models\User;
use Botble\Agent\Models\Agent;
use Botble\HubWarehouse\Models\DepartmentUser;
use Botble\HubWarehouse\Models\HubUser;
use Botble\HubWarehouse\Models\HubWarehouse;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\HubWarehouse\Observers\UserObserver;
use Botble\HubWarehouse\Repositories\Eloquent\HubIssueRepository;
use Botble\HubWarehouse\Repositories\Eloquent\UserAgentRepository;
use Botble\HubWarehouse\Repositories\Eloquent\UserHubRepository;
use Botble\HubWarehouse\Repositories\Interfaces\HubIssueRepositoryInterface;
use Botble\HubWarehouse\Repositories\Interfaces\UserAgentInterface;
use Botble\HubWarehouse\Repositories\Interfaces\UserHubInterface;
use Illuminate\Routing\Events\RouteMatched;

class HubWarehouseServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {

        User::observe(UserObserver::class);
        $this->app->bind(UserHubInterface::class, function () {
            return new UserHubRepository(new HubWarehouse());
        });
        $this->app->bind(UserAgentInterface::class, function () {
            return new UserAgentRepository(new Agent());
        });

        $this->app->bind(HubIssueRepositoryInterface::class, function () {
            return new HubIssueRepository(new HubWarehouse());
        });

        $this
            ->setNamespace('plugins/hub-warehouse')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);


        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-hub-warehouse',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'Quản lý HUB',
                'icon' => 'ti ti-list',
                'permissions' => ['hub-warehouse.index', 'hub-warehouse.all-permissions'],
            ])->registerItem([
                        'id' => 'cms-plugins-hub-warehouse-1',
                        'priority' => 1,
                        'parent_id' => 'cms-plugins-hub-warehouse',
                        'name' => 'Danh sách',
                        'icon' => 'ti ti-list',
                        'url' => route('hub-warehouse.index'),
                        'permissions' => ['hub-warehouse.index', 'hub-warehouse.all-permissions'],
                    ])
                ->registerItem([
                    'id' => 'cms-plugins-warehouse',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-hub-warehouse',
                    'name' => 'Kho hàng',
                    'icon' => 'ti ti-home',
                    'url' => route('hub-stock.index'),
                    'permissions' => ['hub-stock.index', 'hub-warehouse.all-permissions'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-hub-product',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-hub-warehouse',
                    'name' => 'Sản phẩm',
                    'icon' => 'ti ti-app-window-filled',
                    'url' => route('hub-product.index'),
                    'permissions' => ['hub-product.index', 'hub-warehouse.all-permissions'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-proposal-hub-receipt',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-hub-warehouse',
                    'name' => 'Đề xuất nhập kho',
                    'icon' => 'ti ti-download',
                    'url' => route('proposal-hub-receipt.index'),
                    'permissions' => ['proposal-hub-receipt.index', 'hub-warehouse.all-permissions'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-proposal-hub-issue',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-hub-warehouse',
                    'name' => 'Đề xuất xuất kho',
                    'icon' => 'ti ti-upload',
                    'url' => route('proposal-hub-issue.index'),
                    'permissions' => ['proposal-hub-issue.index', 'hub-warehouse.all-permissions'],
                ])->registerItem([
                        'id' => 'cms-plugins-hub-receipt',
                        'priority' => 5,
                        'parent_id' => 'cms-plugins-hub-warehouse',
                        'name' => 'Phiếu nhập kho',
                        'icon' => 'ti ti-clipboard-plus',
                        'url' => route('hub-receipt.index'),
                        'permissions' => ['hub-receipt.index', 'hub-warehouse.all-permissions'],
                    ])
                ->registerItem([
                    'id' => 'cms-plugins-hub-issue',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-hub-warehouse',
                    'name' => 'Phiếu xuất kho',
                    'icon' => 'ti ti-clipboard-check',
                    'url' => route('hub-issue.index'),
                    'permissions' => ['hub-issue.index', 'hub-warehouse.all-permissions'],
                ])
            ;
        });

    }
}
