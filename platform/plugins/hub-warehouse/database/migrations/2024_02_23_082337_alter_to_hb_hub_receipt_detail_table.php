<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hb_hub_receipt_detail', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->string('product_name', 191)->nullable()->change();
            $table->string('sku', 191)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_hub_receipt_detail', function (Blueprint $table) {
            //
        });
    }
};
