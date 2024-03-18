<?php

namespace Botble\NotiAdminPusher\Providers;

use Botble\Base\Facades\Assets;
use Botble\NotiAdminPusher\Models\NotiAdminPusher;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use Telegram\Bot\Laravel\TelegramServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Telegram\Bot\Laravel\Facades\Telegram;

class NotiAdminPusherServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;
    public function register(): void
    {

        AliasLoader::getInstance()->alias('Telegram', Telegram::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/noti-admin-pusher')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes();

        if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(NotiAdminPusher::class, [
                'name',
            ]);
        }

        $this->app['events']->listen(RouteMatched::class, function () {
            Assets::addScriptsDirectly([
                'vendor/core/plugins/noti-admin-pusher/js/noti-admin-pusher.js',
            ],'header');
        });
    }
    public function provides(): array
    {
        return [TelegramServiceProvider::class];
    }
}
