<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('sw_sale_proposal_issues', function (Blueprint $table) {
            $table->id();
            $table->string('general_order_code', 255)->nullable();
            $table->integer('proposal_code');
            $table->foreignId('warehouse_issue_id');
            $table->string('warehouse_name',191)->nullable();
            $table->string('warehouse_address',191)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name',191);
            $table->string('invoice_confirm_name',191)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type')->nullable();
            $table->string('is_warehouse',60)->nullable();
            $table->integer('quantity');
            $table->string('title',191);
            $table->string('description')->nullable();
            $table->date('expected_date');
            $table->date('date_confirm')->nullable();
            $table->string('reason_cancel',191)->nullable();
            $table->string('status',191)->default('pending');
            $table->timestamps();
        });
        Schema::create('sw_sale_proposal_issue_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->foreignId('product_id');
            $table->integer('quantity');
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('sw_sale_proposal_issues');
        Schema::dropIfExists('sw_sale_proposal_issue_details');

    }
};
