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
        Schema::create('sw_sale_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_warehouse_child_id');
            $table->foreignId('product_id');
            $table->integer('quantity')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->integer('quantity_issue')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_sale_product');
    }
};
