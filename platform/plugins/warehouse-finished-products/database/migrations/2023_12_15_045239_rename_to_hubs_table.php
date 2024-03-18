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
        Schema::rename('hubs', 'wfp_hubs');
        Schema::table('wfp_product_issue', function (Blueprint $table) {
            $table->string('proposal_code');
        });
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->string('proposal_code');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('wfp_hubs', 'hubs');
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->dropColumn('proposal_code');
        });
        Schema::table('wfp_product_issue', function (Blueprint $table) {
            $table->dropColumn('proposal_code');
        });
    }
};
