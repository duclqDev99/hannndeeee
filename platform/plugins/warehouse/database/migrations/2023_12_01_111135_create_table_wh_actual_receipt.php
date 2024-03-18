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
        Schema::create('wh_actual_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->string('general_order_code', 50)->nullable();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_name',255);
            $table->string('warehouse_address',255);
            $table->string('invoice_confirm_name',255);
            $table->integer('quantity');
            $table->string('status', 60)->default('pending'); //approved, denied, pending
            $table->timestamps();
        });

        Schema::create('wh_actual_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->integer('material_id')->nullable();
            $table->text('material_code');
            $table->text('material_name')->nullable();
            $table->text('material_unit')->nullable();
            $table->text('material_quantity');
            $table->integer('material_price');
            $table->text('reasoon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_actual_receipt');
        Schema::dropIfExists('wh_actual_receipt_details');
    }
};
