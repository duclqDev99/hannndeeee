<?php

namespace Botble\OverviewReport;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('overview_reports');
        Schema::dropIfExists('overview_reports_translations');
    }
}
