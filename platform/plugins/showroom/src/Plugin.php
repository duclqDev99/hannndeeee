<?php

namespace Botble\Showroom;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('showrooms');
        Schema::dropIfExists('showrooms_translations');
    }
}
