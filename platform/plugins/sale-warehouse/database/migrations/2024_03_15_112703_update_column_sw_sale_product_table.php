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
        if (Schema::hasTable('sw_sale_product')) {
            Schema::table('sw_sale_product', function (Blueprint $table) {
                if (Schema::hasColumn('sw_sale_product', 'sale_warehouse_child_id')) {
                    $table->renameColumn('sale_warehouse_child_id', 'warehouse_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sw_sale_product', function (Blueprint $table) {
            //
        });
    }
};
