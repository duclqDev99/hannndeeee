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
        Schema::create('showroom_proposal_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_issue_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->string('proposal_code', 50);
            $table->string('general_order_code', 50)->nullable();
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
            $table->tinyInteger('is_batch')->default(0);
            $table->string('reason_cancel', 255)->nullable();
            $table->string('status', 60)->default('pending'); //approved, denied, pending, confirm
            $table->timestamps();
        });
        Schema::create('showroom_proposal_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->string('product_name', 255);
            $table->string('sku', 255);
            $table->integer('quantity');
            $table->string('size', 25)->nullable();
            $table->string('color', 25)->nullable();
            $table->tinyInteger('is_batch')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_proposal_issues');
        Schema::dropIfExists('showroom_proposal_issue_detail');
    }
};
