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
        Schema::create('wfp_product_batchs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id');
            $table->foreignId('receipt_id');
            $table->string('batch_code');
            $table->foreignId('product_id');
            $table->string('product_name', 255)->nullable();
            $table->string('sku');
            $table->integer('quantity');
            $table->integer('start_qty');
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_product_batchs');
    }
};
