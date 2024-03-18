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
        Schema::table('wfp_actual_issue', function (Blueprint $table) {
            $table->renameColumn('product_issue_detail_id','product_issue_id');
            $table->dropColumn('product_id');
            $table->dropColumn('sku');
            $table->dropColumn('quantity');
            $table->string('general_order_code', 50)->nullable();
            $table->foreignId('warehouse_issue_id');
            $table->string('warehouse_name', 255)->nullable();
            $table->string('warehouse_address', 255)->nullable();
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_type', 255)->nullable();
            $table->tinyInteger('is_warehouse')->default(1);
            $table->string('status', 60)->default('pending');
        });
        Schema::create('wfp_actual_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->float('price')->default(0);
            $table->integer('quantity');
            $table->string('reasoon', 255)->nullable();
            $table->string('qrcode_id', 255)->nullable();
            $table->tinyInteger('is_batch')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_actual_issue_detail');
        Schema::table('wfp_actual_issue', function (Blueprint $table) {
            //
        });
    }
};
