<?php

namespace Botble\WarehouseFinishedProducts;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('warehouse_finished_products');
        Schema::dropIfExists('warehouse_finished_products_translations');
    }
}
