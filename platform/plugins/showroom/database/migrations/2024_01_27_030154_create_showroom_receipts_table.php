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

        Schema::create('showroom_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_receipt_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 255);
            $table->string('general_order_code', 50)->nullable();
            $table->integer('quantity')->default(0);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('reason_cancel', 255)->nullable();
            $table->string('status', 60)->default('pending');
            $table->tinyInteger('from_hub_warehouse')->default(0);
            $table->integer('receipt_code');
            $table->timestamps();


        });
        Schema::create('showroom_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->bigInteger('price')->default(0);
            $table->string('color',60)->nullable();
            $table->string('size',60)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('batch_id')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_receipts');
        Schema::dropIfExists('showroom_receipt_detail');
    }
};
