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
        Schema::create('wfp_proposal_good_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('isser_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_receipt_id');
            $table->string('warehouse_type',255);
            $table->string('general_order_code', 50);
            $table->tinyInteger('is_warehouse')->default(1);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('wfp_proposal_good_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_good_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->timestamps();
        });

        Schema::create('wfp_good_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('isser_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_receipt_id');
            $table->string('warehouse_type',255);
            $table->string('general_order_code', 50);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('wfp_good_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('good_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_proposal_good_receipt');
        Schema::dropIfExists('wfp_proposal_good_receipt_detail');
        Schema::dropIfExists('wfp_good_receipt');
        Schema::dropIfExists('wfp_good_receipt_detail');
    }
};
