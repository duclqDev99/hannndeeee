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
        Schema::create('wfp_product_batch_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id');
            $table->foreignId('product_id');
            $table->foreignId('qrcode');
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->integer('start_qty');
        });

        Schema::table('wfp_product_batchs', function (Blueprint $table){
            $table->dropColumn('product_id');
            $table->dropColumn('product_name');
            $table->dropColumn('sku');
            $table->foreignId('qrcode')->after('receipt_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_product_batch_details');
    }
};
