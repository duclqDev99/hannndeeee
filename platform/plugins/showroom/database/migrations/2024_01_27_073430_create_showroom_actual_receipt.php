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
        Schema::create('showroom_actual_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->string('image', 60)->nullable();
            $table->timestamps();
        });
        Schema::create('showroom_actual_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->bigInteger('price')->default(0);
            $table->foreignId('qrcode_id')->default(0);
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
        Schema::dropIfExists('showroom_actual_receipt');
        Schema::dropIfExists('showroom_actual_receipt_detail');
    }
};
