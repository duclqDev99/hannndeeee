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
        Schema::table('wh_material_batchs', function (Blueprint $table) {
            $table->integer('start_qty')->after('quantity');
        });
        Schema::table('wh_material_receipt_confirm', function (Blueprint $table) {
            $table->boolean('is_purchase_goods')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
