<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sw_sale_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_receipt_id');
            $table->foreignId('hub_issue_id');
            $table->integer('receipt_code');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 255);
            $table->string('general_order_code', 50)->nullable();
            $table->integer('quantity');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending'); 
            $table->timestamps();
        });
        Schema::create('sw_sale_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_sale_receipts');
        Schema::dropIfExists('sw_sale_receipt_detail');
    }
};
