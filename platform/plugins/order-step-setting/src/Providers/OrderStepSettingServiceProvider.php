<?php

namespace Botble\OrderStepSetting\Providers;

use Botble\OrderStepSetting\Models\OrderStepSetting;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;

class OrderStepSettingServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/order-step-setting')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        // if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
        //     \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(OrderStepSetting::class, [
        //         'name',
        //     ]);
        // }
        $this->app['events']->listen(RouteMatched::class, function () {
            // DashboardMenu::registerItem([
            //     'id' => 'cms-plugins-order-step-setting',
            //     'priority' => 5,
            //     'parent_id' => null,
            //     'name' => 'Cài đặt tiến trình đơn hàng',
            //     'icon' => 'fa-solid fa-gear',
            //     'url' => route('order-step-setting.index'),
            //     'permissions' => ['order-step-setting.index'],
            // ]);
        });
    }
}
