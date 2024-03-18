<?php

namespace Botble\OrderTransaction;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('order_transactions');
        Schema::dropIfExists('order_transactions_translations');
    }
}
