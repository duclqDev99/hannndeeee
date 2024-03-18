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
        Schema::dropIfExists('wfp_proposal_good_receipt');
        Schema::dropIfExists('wfp_proposal_good_receipt_detail');

        Schema::create('wfp_proposal_receipt_products', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 50)->nullable();
            $table->string('proposal_code', 50);
            $table->foreignId('warehouse_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->foreignId('isser_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('wh_departure_id')->nullable();
            $table->string('wh_departure_name', 255)->nullable();
            $table->tinyInteger('is_warehouse')->default(1);
            $table->integer('quantity');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('reasoon_cancel', 255)->nullable();
            $table->foreignId('proposal_issue_id')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('wfp_proposal_receipt_product_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->foreignId('processing_house_id')->nullable();
            $table->string('processing_house_name')->nullable();
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_proposal_receipt_products');
        Schema::dropIfExists('wfp_proposal_receipt_product_detail');
    }
};
