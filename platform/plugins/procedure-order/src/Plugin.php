<?php

namespace Botble\ProcedureOrder;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('procedure_orders');
        Schema::dropIfExists('procedure_orders_translations');
    }
}
