<?php

namespace Botble\InventoryDiscountPolicy;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('inventory_discount_policies');
        Schema::dropIfExists('inventory_discount_policies_translations');
    }
}
