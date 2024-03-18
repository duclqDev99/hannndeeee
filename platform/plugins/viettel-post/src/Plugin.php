<?php

namespace Botble\ViettelPost;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'shipping_viettel_post_status',
            'shipping_viettel_post_test_key',
            'shipping_viettel_post_production_key',
            'shipping_viettel_post_sandbox',
            'shipping_viettel_post_logging',
            'shipping_viettel_post_cache_response',
            'shipping_viettel_post_webhooks',
        ]);
    }
}
