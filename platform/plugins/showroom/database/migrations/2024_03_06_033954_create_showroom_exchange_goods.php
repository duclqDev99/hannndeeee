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
        Schema::create('showroom_exchange_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_id');
            $table->integer('total_quantity');
            $table->integer('total_amount');
            $table->string('status', 60)->default('waiting');
            $table->timestamps();
        });

        Schema::create('showroom_exchange_goods_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id');
            $table->foreignId('order_id');
            $table->foreignId('qr_id_change');
            $table->foreignId('price_product_change');
            $table->foreignId('qr_id_pay');
            $table->foreignId('price_product_pay');
            $table->string('option', 60)->default('equal');
            $table->integer('price_additional')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_exchange_goods');
    }
};
