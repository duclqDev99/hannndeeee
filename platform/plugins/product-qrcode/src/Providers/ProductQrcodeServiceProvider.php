<?php

namespace Botble\ProductQrcode\Providers;

use Botble\Base\Facades\Assets;
use Botble\ProductQrcode\Models\ProductQrcode;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class ProductQrcodeServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {

        Assets::addStylesDirectly([
            'vendor/core/plugins/product-qrcode/css/style.css',
        ]);

        $this
            ->setNamespace('plugins/product-qrcode')
            ->loadHelpers('random')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();



        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-product-qrcode',
                'priority' => 3,
                'parent_id' => null,
                'name' => 'plugins/product-qrcode::product-qrcode.name',
                'icon' => 'fa fa-qrcode',
                'url' => route('product-qrcode.index'),
                'permissions' => ['product-qrcode.index'],
            ]);
        });
    }
}
