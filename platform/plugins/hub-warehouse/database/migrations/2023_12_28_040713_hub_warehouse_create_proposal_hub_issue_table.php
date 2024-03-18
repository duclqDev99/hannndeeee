<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hb_proposal_hub_issues', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 50)->nullable();
            $table->string('proposal_code', 50);
            $table->foreignId('warehouse_issue_id');
            $table->string('warehouse_name', 255);
            $table->string('hub_address', 255);
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type', 255)->nullable();
            $table->tinyInteger('is_warehouse')->default(1);
            $table->integer('quantity');
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('reasoon_cancel', 255)->nullable();
            $table->foreignId('proposal_receipt_id')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('hb_proposal_hub_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->string('product_name', 255);
            $table->string('sku', 255);
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('attribute',255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hb_proposal_hub_issues');
        Schema::dropIfExists('hb_proposal_hub_issue_detail');
    }
};
