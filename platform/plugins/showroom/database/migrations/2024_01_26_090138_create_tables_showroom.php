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
        Schema::create('showroom_proposal_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 50)->nullable();
            $table->string('proposal_code', 50);
            $table->foreignId('warehouse_receipt_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type', 255)->nullable();
            $table->integer('quantity');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('reason_cancel', 255)->nullable();
            $table->foreignId('proposal_issue_id')->nullable();
            $table->string('status', 60)->default('pending'); //approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('showroom_proposal_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->string('product_name', 255);
            $table->string('sku', 255);
            $table->bigInteger('price')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_proposal_receipts');
        Schema::dropIfExists('showroom_proposal_receipt_detail');
    }
};
