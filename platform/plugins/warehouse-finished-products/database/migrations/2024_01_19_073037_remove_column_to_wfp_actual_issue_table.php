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
        if (Schema::hasColumn('wfp_proposal_product_issue', 'is_batch'))
        {

            Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
                $table->dropColumn('is_batch');
                $table->dropColumn('is_odd');
            });
        }
        if (Schema::hasColumn('wfp_proposal_product_issue_detail', 'is_batch'))
        {

            Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
                $table->dropColumn('is_batch');
            });
        }
        if (Schema::hasColumn('wfp_product_issue', 'proposal_code'))
        {

            Schema::table('wfp_product_issue', function (Blueprint $table) {
                $table->renameColumn('proposal_code','issue_code');
            });
        }
        if (Schema::hasColumn('wfp_product_issue_detail', 'is_batch'))
        {

            Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
                $table->dropColumn('is_batch');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            //
        });
    }
};
