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
        Schema::table('wfp_receipt_products_detail', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('receipt_id');
        });

        Schema::table('wfp_proposal_receipt_product_detail', function(Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();   
        });

        Schema::table('wfp_receipt_products_detail', function(Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();   
        });

        Schema::table('wfp_actual_receipt_detail', function(Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();   
        });

        Schema::table('wfp_proposal_product_issue_detail', function(Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
