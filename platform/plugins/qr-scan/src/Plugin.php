<?php

namespace Botble\QrScan;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('qr_scans_translations');
    }
}
