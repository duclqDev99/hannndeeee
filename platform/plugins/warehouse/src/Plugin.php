<?php

namespace Botble\Warehouse;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('warehouses_translations');
    }
}
