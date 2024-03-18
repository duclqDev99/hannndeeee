<?php

namespace Botble\NotiAdminPusher;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('noti_admin_pushers');
        Schema::dropIfExists('noti_admin_pushers_translations');
    }
}
