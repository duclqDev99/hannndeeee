<?php

namespace Botble\HubWarehouse;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('hub_warehouses');
        Schema::dropIfExists('hub_warehouses_translations');
    }
}
