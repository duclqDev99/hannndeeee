<?php

namespace Botble\WidgetCustom\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Theme\Events\RenderingAdminBar;
use Botble\Theme\Facades\AdminBar;
use Botble\WidgetCustom\Facades\WidgetGroup;
use Botble\WidgetCustom\Factories\WidgetFactory;
use Botble\WidgetCustom\Models\Widget;
use Botble\WidgetCustom\Repositories\Eloquent\WidgetRepository;
use Botble\WidgetCustom\Repositories\Interfaces\WidgetInterface;
use Botble\WidgetCustom\WidgetGroupCollection;
use Botble\WidgetCustom\Widgets\Address;
use Botble\WidgetCustom\Widgets\CoreSimpleMenu;
use Botble\WidgetCustom\Widgets\Text;
use Illuminate\Contracts\Foundation\Application;

class WidgetServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind(WidgetInterface::class, function () {
            return new WidgetRepository(new Widget());
        });

        $this->app->bind('botble.widget-custom', function (Application $app) {
            return new WidgetFactory($app);
        });

        $this->app->singleton('botble.widget-custom-group-collection', function (Application $app) {
            return new WidgetGroupCollection($app);
        });

        $this
            ->setNamespace('plugins/widget-custom')
            ->loadHelpers();
    }

    public function boot(): void
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadMigrations()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        $this->app->booted(function () {
            WidgetGroup::setGroup([
                'id' => 'footer_sidebar_custom_left',
                'name' => __('Footer ở bên trái'),
                'description' => __('Footer ở bên trái'),
            ]);
            WidgetGroup::setGroup([
                'id' => 'footer_sidebar_custom_center',
                'name' => __('Footer ở giữa 1'),
                'description' => __('Footer ở giữa 1'),
            ]);
            WidgetGroup::setGroup([
                'id' => 'footer_sidebar_custom_center_end',
                'name' => __('Footer ở giữa 2'),
                'description' => __('Footer ở giữa 2'),
            ]);
            WidgetGroup::setGroup([
                'id' => 'footer_sidebar_custom_right',
                'name' => __('Footer ở bên phải'),
                'description' => __('Footer ở bên phải'),
            ]);

            
            
            // register_sidebar([
            //     'id' => 'footer_sidebar_custom_1',
            //     'name' => __('Footer ở giữa'),
            //     'description' => __('Footer ở giữa'),
            // ]);
            
            // app()->booted(function () {
            //     remove_sidebar('footer_sidebar');
            //     remove_sidebar('primary_sidebar');
            // });

            register_widget_custom(Address::class);
            register_widget_custom(CoreSimpleMenu::class);
            register_widget_custom(Text::class);
        });

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-core-widget-custom',
                    'priority' => 8,
                    'parent_id' => 'cms-core-appearance',
                    'name' => 'Widget custom',
                    'route' => 'widgets-custom.index',
                ]);
        });

        $this->app['events']->listen(RenderingAdminBar::class, function () {
            AdminBar::registerLink(
                trans('Widget custom'),
                route('widgets-custom.index'),
                'appearance',
                'widgets-custom.index'
            );
        });
    }
}
