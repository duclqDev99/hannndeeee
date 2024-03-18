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
        Schema::create('hb_actual_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 50)->nullable();
            $table->foreignId('receipt_id');
            $table->foreignId('warehouse_receipt_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type', 255)->nullable();
            $table->tinyInteger('is_warehouse')->default(0);
            $table->integer('quantity');
            $table->string('status', 60)->default('pending');
            $table->timestamps();
        });
        Schema::create('hb_actual_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->string('reasoon', 255)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hb_actual_receipts');
        Schema::dropIfExists('hb_actual_receipt_detail');
    }
};
