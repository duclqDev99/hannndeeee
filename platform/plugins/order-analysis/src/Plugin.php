<?php

namespace Botble\OrderAnalysis;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('order_analyses');
        Schema::dropIfExists('order_analyses_translations');
    }
}
