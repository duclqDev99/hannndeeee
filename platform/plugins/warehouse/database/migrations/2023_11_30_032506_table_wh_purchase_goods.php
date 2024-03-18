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
        Schema::create('wh_purchase_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('invoice_issuer_name', 255)->nullable();
            $table->string('invoice_confirm_name',255)->nullable();
            $table->string('general_order_code', 50);
            $table->string('code', 50);
            $table->string('document_number', 255)->nullable();
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending
            $table->timestamps();
        });
        Schema::create('wh_purchase_goods_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->text('supplier_name', 255);
            $table->foreignId('supplier_id');
            $table->text('material_code');
            $table->text('material_name');
            $table->text('material_unit');
            $table->text('material_quantity');
            $table->integer('material_price');
        });

        Schema::create('wh_receipt_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('invoice_issuer_name', 255)->nullable();
            $table->string('invoice_confirm_name',255)->nullable();
            $table->string('general_order_code', 50);
            $table->foreignId('proposal_id');
            $table->string('document_number', 255)->nullable();
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending
            $table->timestamps();
        });
        Schema::create('wh_receipt_goods_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->text('supplier_name', 255);
            $table->foreignId('supplier_id');
            $table->text('material_code');
            $table->text('material_name');
            $table->text('material_unit');
            $table->text('material_quantity');
            $table->integer('material_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_purchase_goods');
        Schema::dropIfExists('wh_purchase_goods_details');
        Schema::dropIfExists('wh_receipt_goods');
        Schema::dropIfExists('wh_receipt_goods_details');
    }
};
