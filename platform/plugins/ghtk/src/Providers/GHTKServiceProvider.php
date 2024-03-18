<?php

namespace Botble\GHTK\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\GHTK\Http\Middleware\WebhookMiddleware;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;

class GHTKServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->setNamespace('plugins/ghtk')->loadHelpers();
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
            ->publishAssets()
            ->loadMigrations();

        $this->app['events']->listen(RouteMatched::class, function () {
            $this->app['router']->aliasMiddleware('ghtk.webhook', WebhookMiddleware::class);
        });

        $config = $this->app['config'];
        if (! $config->has('logging.channels.ghtk')) {
            $config->set([
                'logging.channels.ghtk' => [
                    'driver' => 'daily',
                    'path' => storage_path('logs/ghtk.log'),
                ],
            ]);
        }

        $this->app->register(HookServiceProvider::class);
        $this->app->register(CommandServiceProvider::class);
    }
}
