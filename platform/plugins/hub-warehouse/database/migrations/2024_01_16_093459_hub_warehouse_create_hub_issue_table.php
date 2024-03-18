<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hb_hub_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_issue_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type',255);
            $table->string('general_order_code', 50)->nullable();
            $table->string('reason',255)->nullable();
            $table->tinyInteger('from_proposal_receipt')->default(0);
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date');
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending, confirm
            $table->timestamps();
        });

        Schema::create('hb_hub_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_issue_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku',25)->nullable();
            $table->string('size',25)->nullable();
            $table->string('color');
            $table->string('is_batch');
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hb_hub_issues');
        Schema::dropIfExists('hb_hub_issue_detail');
    }
};
