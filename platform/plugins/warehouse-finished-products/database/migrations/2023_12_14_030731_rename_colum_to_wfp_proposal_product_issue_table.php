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
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->renameColumn('isser_id', 'issuer_id');
            $table->string('general_order_code')->nullable()->change();
        });
        Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
           $table->string('attribute', 255)->nullable();
           $table->string('product_code', 255)->nullable();
        });
        Schema::table('wfp_product_issue', function (Blueprint $table) {
            $table->renameColumn('isser_id', 'issuer_id');
            $table->string('general_order_code')->nullable()->change();
        });
        Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
           $table->string('attribute', 255)->nullable();
           $table->string('product_code', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
