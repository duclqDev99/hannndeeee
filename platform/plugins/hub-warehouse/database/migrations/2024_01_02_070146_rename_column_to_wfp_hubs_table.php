<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('wfp_hubs')) {
            Schema::rename('wfp_hubs', 'hb_hubs');
        }
        Schema::dropIfExists('hub_warehouses');
        Schema::dropIfExists('hub_warehouses_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_hubs', function (Blueprint $table) {
            //
        });
    }
};
