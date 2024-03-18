<?php

namespace Botble\Warehouse\Providers;

use Botble\Warehouse\Facades\MaterialHelper;
use Botble\Warehouse\Models\Warehouse;
use Botble\Base\Facades\DashboardMenu;
use Botble\Warehouse\Models\Category;
use Botble\Warehouse\Repositories\Eloquent\CategoryRepository;
use Botble\Warehouse\Repositories\Interfaces\CategoryInterface;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Botble\Slug\Facades\SlugHelper;
use Botble\Warehouse\Models\Material;
use Botble\Warehouse\Models\TypeMaterial;
use Botble\Warehouse\Repositories\Eloquent\TypeMaterialRepository;
use Botble\Warehouse\Repositories\Interfaces\MaterialInterface;
use Botble\Warehouse\Repositories\Interfaces\TypeMaterialInterface;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\URL;

class WarehouseServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(TypeMaterialInterface::class, function () {
            return new TypeMaterialRepository(new TypeMaterial());
        });

    }

    public function boot(): void
    {


        $this
            ->setNamespace('plugins/warehouse')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web', 'api']);



        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-material',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'Kho nguyên phụ liệu',
                'icon' => 'ti ti-archive',
                'permissions' => ['warehouse.index'],
            ])->registerItem([
                        'id' => 'cms-plugins-type_material',
                        'priority' => 1,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Loại nguyên phụ liệu',
                        'icon' => 'ti ti-list',
                        'url' => route('type_material.index'),
                        'permissions' => ['type_material.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-material-1',
                        'priority' => 2,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Nguyên phụ liệu',
                        'icon' => 'ti ti-server',
                        'url' => route('material.index'),
                        'permissions' => ['material.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-supplier',
                        'priority' => 3,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Nhà cung cấp',
                        'icon' => 'ti ti-truck-delivery',
                        'url' => route('supplier.index'),
                        'permissions' => ['supplier.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-processing_house',
                        'priority' => 4,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Nhà gia công',
                        'icon' => 'ti ti-home',
                        'url' => route('processing_house.index'),
                        'permissions' => ['processing_house.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-warehouse-material',
                        'priority' => 5,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Danh sách kho',
                        'icon' => 'ti ti-list',
                        'url' => route('warehouse-material.index'),
                        'permissions' => ['warehouse-material.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-material-batch',
                        'priority' => 6,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Lô hàng của kho',
                        'icon' => 'ti ti-package',
                        'url' => route('material-batch.index'),
                        'permissions' => ['material-batch.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-proposal-purchase-goods',
                        'priority' => 7,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Đơn đề xuất mua hàng',
                        'icon' => 'ti ti-shopping-cart',
                        'url' => route('proposal-purchase-goods.index'),
                        'permissions' => ['proposal-purchase-goods.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-receipt-purchase-goods',
                        'priority' => 8,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Phiếu mua hàng',
                        'icon' => 'ti ti-shopping-cart-check',
                        'url' => route('receipt-purchase-goods.index'),
                        'permissions' => ['receipt-purchase-goods.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-mtproposal',
                        'priority' => 9,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Đề xuất nhập kho',
                        'icon' => 'ti ti-download',
                        'url' => route('material-proposal-purchase.index'),
                        'permissions' => ['material-proposal-purchase.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-material-receipt-confirm',
                        'priority' => 10,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Phiếu nhập kho',
                        'icon' => 'ti ti-clipboard-plus',
                        'url' => route('material-receipt-confirm.index'),
                        'permissions' => ['material-receipt-confirm.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-material_plan',
                        'priority' => 11,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Đề xuất xuất kho',
                        'icon' => 'ti ti-upload',
                        'url' => route('proposal-goods-issue.index'),
                        'permissions' => ['proposal-goods-issue.index'],
                    ])->registerItem([
                        'id' => 'cms-plugins-check_inventory',
                        'priority' => 12,
                        'parent_id' => 'cms-plugins-material',
                        'name' => 'Phiếu xuất kho',
                        'icon' => 'ti ti-clipboard-check',
                        'url' => route('goods-issue-receipt.index'),
                        'permissions' => ['goods-issue-receipt.index'],
                    ])
            ;
        });

        if (str_ends_with(request()->getHost(), '.ngrok-free.app')) {
            URL::forceScheme('https');
        }
    }
}
