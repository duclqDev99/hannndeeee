<?php

namespace Botble\OrderTransaction\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Supports\ServiceProvider;

class OrderTransactionServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/order-transaction')
            ->loadHelpers()
            ->loadMigrations()
            ->loadRoutes(['web','api']);
        Assets::addScriptsDirectly(['vendor/core/plugins/order-transaction/js/transaction.js']);
    }
}
