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
        Schema::table('wfp_proposal_receipt_products', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });
        Schema::table('wfp_proposal_product_issue', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });

        Schema::table('hb_proposal_hub_recepits', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });
        Schema::table('hb_proposal_hub_issues', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });

        Schema::table('showroom_proposal_receipts', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });
        Schema::table('showroom_proposal_issues', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });

        Schema::table('agent_proposal_receipts', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });
        Schema::table('agent_proposal_issues', function(Blueprint $table){
            $table->string('proposal_code', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
