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
        Schema::table('agent_issues', function (Blueprint $table) {
            $table->foreignId('warehouse_issue_id');
            $table->foreignId('proposal_id');
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255)->nullable();
            $table->foreignId('issuer_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type',255)->nullable();
            $table->string('general_order_code', 50)->nullable();
            $table->string('reason',255)->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date');
            $table->integer('issue_code');
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending')->change();//approved, denied, pending, confirm
            $table->dropColumn('name');
        });

        Schema::create('agent_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_issue_id');
            $table->foreignId('product_id');
            $table->string('product_name');
            $table->string('sku',25)->nullable();
            $table->string('size',25)->nullable();
            $table->string('color');
            $table->bigInteger('price')->default(0);
            $table->integer('quantity');
            $table->timestamps();
        });
        Schema::dropIfExists('agent_issues_translations');


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_issues', function (Blueprint $table) {
            //
        });
        Schema::dropIfExists('agent_issue_detail');
    }
};
