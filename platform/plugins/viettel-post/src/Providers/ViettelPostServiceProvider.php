<?php

namespace Botble\ViettelPost\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\ViettelPost\Http\Middleware\WebhookMiddleware;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class ViettelPostServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->setNamespace('plugins/viettel-post')->loadHelpers();
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadAndPublishConfigurations(['general'])
            ->publishAssets();

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app['router']->aliasMiddleware('viettel-post.webhook', WebhookMiddleware::class);
        });

        $config = $this->app['config'];
        if (! $config->has('logging.channels.viettel-post')) {
            $config->set([
                'logging.channels.viettel-post' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/viettel-post.log'),
                ],
            ]);
        }

        $this->app->register(HookServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
    }
}
