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
        Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('wfp_product_issue', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
            //
        });
    }
};
