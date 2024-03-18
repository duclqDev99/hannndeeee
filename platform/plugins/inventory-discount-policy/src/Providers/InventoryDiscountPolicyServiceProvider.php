<?php

namespace Botble\InventoryDiscountPolicy\Providers;

use Botble\InventoryDiscountPolicy\Models\InventoryDiscountPolicy;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class InventoryDiscountPolicyServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/inventory-discount-policy')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();
        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-discount-policy',
                'priority' => 5,
                'parent_id' => 'cms-plugins-sale-warehouse-index',
                'name' => 'Chính sách giảm giá',
                'icon' => 'fa fa-list',
                'url' => route('inventory-discount-policy.index'),
                'permissions' => ['inventory-discount-policy.index'],
            ]);
        });
    }
}
