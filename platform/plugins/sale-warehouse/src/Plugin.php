<?php

namespace Botble\SaleWarehouse;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('sale_warehouses');
        Schema::dropIfExists('sale_warehouses_translations');
    }
}
