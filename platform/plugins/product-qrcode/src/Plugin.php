<?php

namespace Botble\ProductQrcode;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('product_qrcodes');
        Schema::dropIfExists('product_qrcodes_translations');
    }
}
