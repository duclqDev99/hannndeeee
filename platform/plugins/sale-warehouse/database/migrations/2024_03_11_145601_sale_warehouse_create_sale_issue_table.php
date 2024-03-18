<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('sw_sale_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_issue_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name',191)->nullable();
            $table->string('warehouse_address',191)->nullable();
            $table->string('general_order_code', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name',191);
            $table->string('invoice_confirm_name',191)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type')->nullable();
            $table->integer('issue_code');
            $table->integer('quantity');
            $table->date('expected_date');
            $table->date('date_confirm')->nullable();
            $table->string('reason_cancel',191)->nullable();
            $table->string('title',191);
            $table->string('description')->nullable();
            $table->string('status',191)->default('pending');
            $table->timestamps();
        });

        Schema::create('sw_sale_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_isue_id');
            $table->foreignId('product_id');
            $table->integer('quantity');
            $table->integer('quantity_scan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_sale_issues');
        Schema::dropIfExists('sw_sale_issue_detail');
    }
};
