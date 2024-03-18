<?php

namespace Botble\GHTK;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'shipping_ghtk_status',
            'shipping_ghtk_test_key',
            'shipping_ghtk_production_key',
            'shipping_ghtk_sandbox',
            'shipping_ghtk_logging',
            'shipping_ghtk_cache_response',
            'shipping_ghtk_webhooks',
        ]);
    }
}
