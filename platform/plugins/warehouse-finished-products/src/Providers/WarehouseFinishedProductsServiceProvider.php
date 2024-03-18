<?php

namespace Botble\WarehouseFinishedProducts\Providers;

use Botble\ACL\Models\User;
use Botble\WarehouseFinishedProducts\Models\WarehouseFinishedProducts;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\WarehouseFinishedProducts\Repositories\Eloquent\UserWarehouseRepository;
use Botble\WarehouseFinishedProducts\Repositories\Interfaces\UserWarehouseInterface;
use Illuminate\Routing\Events\RouteMatched;

class WarehouseFinishedProductsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->app->bind(UserWarehouseInterface::class, function () {
            return new UserWarehouseRepository(new WarehouseFinishedProducts());
        });


        $this
            ->setNamespace('plugins/warehouse-finished-products')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);


        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-warehouse-finished-products',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'Kho thành phẩm',
                'icon' => 'ti ti-archive',
                'url' => route('warehouse-finished-products.index'),
                'permissions' => ['warehouse-finished-products.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-finished-products',
                'priority' => 1,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Thành phẩm',
                'icon' => 'ti ti-server',
                'url' => route('finished-products.index'),
                'permissions' => ['finished-products.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-proposal-good-receipts',
                'priority' => 3,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Đề xuất nhập kho',
                'icon' => 'ti ti-download',
                'url' => route('proposal-receipt-products.index'),
                'permissions' => ['proposal-receipt-products.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-good-receipts',
                'priority' => 4,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Phiếu nhập kho',
                'icon' => 'ti ti-clipboard-check',
                'url' => route('receipt-product.index'),
                'permissions' => ['receipt-product.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-proposal-product-issue',
                'priority' => 3,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Đề xuất xuất kho',
                'icon' => 'ti ti-upload',
                'url' => route('proposal-product-issue.index'),
                'permissions' => ['proposal-product-issue.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-product-issue',
                'priority' => 4,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Phiếu xuất kho',
                'icon' => 'ti ti-clipboard-check',
                'url' => route('product-issue.index'),
                'permissions' => ['product-issue.index'],
            ])
            ->registerItem([
                'id' => 'cms-plugins-warehouse-finished',
                'priority' => 0,
                'parent_id' => 'cms-plugins-warehouse-finished-products',
                'name' => 'Danh sách kho',
                'icon' => 'ti ti-list',
                'url' => route('warehouse-finished-products.index'),
                'permissions' => ['warehouse-finished-products.index'],
            ]);
        });
    }
}
