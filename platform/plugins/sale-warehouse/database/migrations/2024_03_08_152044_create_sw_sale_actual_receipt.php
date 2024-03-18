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
        Schema::create('sw_sale_actual_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->text('image')->nullable();
            $table->timestamps();
        });
        Schema::create('sw_sale_actual_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->foreignId('qrcode_id')->nullable();
            $table->foreignId('batch_id')->nullable();
            $table->integer('quantity');
            $table->string('reason', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_sale_actual_receipt');
        Schema::dropIfExists('sw_sale_actual_receipt_detail');
    }
};
