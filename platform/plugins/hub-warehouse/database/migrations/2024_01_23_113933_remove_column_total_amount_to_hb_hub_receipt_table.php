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
        Schema::table('hb_hub_receipt', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('hb_hub_receipt_detail', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('hb_proposal_hub_issue_detail', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
        Schema::table('hb_proposal_hub_recepit_detail', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_hub_receipt', function (Blueprint $table) {
            //
        });
    }
};
