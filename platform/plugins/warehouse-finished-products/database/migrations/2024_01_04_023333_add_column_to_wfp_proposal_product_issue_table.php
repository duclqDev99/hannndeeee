<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->foreignId('hub_request_id')->nullable();
        });
        Schema::table('hb_proposal_hub_recepits', function (Blueprint $table) {
            $table->foreignId('product_issue_id')->nullable();
            $table->renameColumn('hub_name', 'warehouse_name');
        });
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
