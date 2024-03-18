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
        Schema::create('agent_receipt_products_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->foreignId('product_id');
            $table->foreignId('processing_house_id')->nullable();
            $table->string('processing_house_name')->nullable();
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->string('color', 25)->nullable();
            $table->string('size', 25)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_receipt_products_detail');
    }
};
