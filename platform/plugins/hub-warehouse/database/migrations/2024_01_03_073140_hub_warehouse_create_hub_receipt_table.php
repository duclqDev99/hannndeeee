<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hb_hub_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_receipt_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type',255);
            $table->string('general_order_code', 50)->nullable();
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('hb_hub_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hb_hub_receipt');
        Schema::dropIfExists('hb_hub_receipt_detail');
    }
};
