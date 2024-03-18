<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_proposal_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 50)->nullable();
            $table->string('proposal_code', 50);
            $table->foreignId('warehouse_receipt_id');
            $table->string('hub_name', 255);
            $table->string('hub_address', 255);
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type', 255)->nullable();
            $table->tinyInteger('is_warehouse')->default(1);
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
        Schema::create('agent_proposal_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->string('product_name', 255);
            $table->string('sku', 255);
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('color',25)->nullable();
            $table->string('size',25)->nullable();
            $table->timestamps();
        });

        Schema::create('agent_receipts', function (Blueprint $table) {
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
            $table->string('reason_cancel', 255)->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();


        });
        Schema::create('agent_receipt_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_receipt_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->string('color',25)->nullable();
            $table->string('size',25)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_proposal_agent_receipts');
        Schema::dropIfExists('proposal_agent_receipt_detail');
        Schema::dropIfExists('agent_receipts');
        Schema::dropIfExists('agent_receipt_detail');
    }
};
