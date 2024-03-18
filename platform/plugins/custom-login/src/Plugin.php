<?php

namespace Botble\CustomLogin;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('custom_logins');
        Schema::dropIfExists('custom_logins_translations');
    }
}
