<?php

namespace Botble\SaleWarehouse\Providers;

use Botble\SaleWarehouse\Models\SaleWarehouse;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\SaleWarehouse\Repositories\Eloquent\SaleIssueRepository;
use Botble\SaleWarehouse\Repositories\Eloquent\SaleUserRepository;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleIssueRepositoryInterface;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleUserInterface;
use Illuminate\Routing\Events\RouteMatched;

class SaleWarehouseServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->app->bind(SaleUserInterface::class, function () {
            return new SaleUserRepository(new SaleWarehouse());
        });
        $this->app->bind(SaleIssueRepositoryInterface::class, function () {
            return new SaleIssueRepository(new SaleWarehouse());
        });
        $this
            ->setNamespace('plugins/sale-warehouse')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ;

        // if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
        //     \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(SaleWarehouse::class, [
        //         'name',
        //     ]);
        // }

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-sale-warehouse-index',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'Kho sale',
                'icon' => 'ti ti-discount',
                'permissions' => ['sale-warehouse-index.index'],
            ])->registerItem([
                'id' => 'cms-plugins-sale-warehouse',
                'priority' => 1,
                'parent_id' => 'cms-plugins-sale-warehouse-index',
                'name' => 'Danh sách',
                'icon' => 'ti ti-list',
                'url' => route('sale-warehouse.index'),
                'permissions' => addAllPermissionSaleWarehouse(['sale-warehouse.index']),
            ])
                ->registerItem([
                    'id' => 'cms-plugins-sale-warehouse-child',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-sale-warehouse-index',
                    'name' => 'Kho hàng',
                    'icon' => 'ti ti-archive',
                    'url' => route('sale-warehouse-child.index'),
                    'permissions' =>  addAllPermissionSaleWarehouse(['sale-warehouse-child.index']),
                ])
                ->registerItem([
                    'id' => 'cms-plugins-sale-warehouse-product',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-sale-warehouse-index',
                    'name' => 'Sản phẩm',
                    'icon' => 'ti ti-app-window-filled',
                    'url' => route('sale-warehouse-product.index'),
                    'permissions' =>  addAllPermissionSaleWarehouse(['sale-product.index']),
                ])
                ->registerItem([
                    'id' => 'cms-plugins-sale-receipt',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-sale-warehouse-index',
                    'name' => 'Phiếu nhập kho',
                    'icon' => 'ti ti-clipboard-plus',
                    'url' => route('sale-receipt.index'),
                    'permissions' =>  addAllPermissionSaleWarehouse(['sale-receipt.index']),
                ])
                ->registerItem([
                    'id' => 'cms-plugins-sale-proposal-issue',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-sale-warehouse-index',
                    'name' => 'Đề xuất xuất kho',
                    'icon' => 'ti ti-upload',
                    'url' => route('sale-proposal-issue.index'),
                    'permissions' => addAllPermissionSaleWarehouse( ['sale-proposal-issue.index']),
                ])->
                registerItem([
                    'id' => 'cms-plugins-sale-issue',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-sale-warehouse-index',
                    'name' => 'Phiếu xuất kho',
                    'icon' => 'ti ti-clipboard-check',
                    'url' => route('sale-issue.index'),
                    'permissions' =>  addAllPermissionSaleWarehouse(['sale-issue.index']),
                ])
                ;
        });
    }
}
