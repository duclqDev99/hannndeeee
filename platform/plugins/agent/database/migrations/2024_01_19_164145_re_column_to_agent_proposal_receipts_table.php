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
        Schema::table('agent_proposal_receipts', function (Blueprint $table) {
            $table->dropColumn('warehouse_type');
            $table->dropColumn('warehouse_id');
            $table->string('warehouse_address',255)->nullable()->change();
        });
        Schema::table('agent_proposal_receipt_detail', function (Blueprint $table) {
            $table->dropColumn('size');
            $table->dropColumn('color');
            $table->dropColumn('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_proposal_receipts', function (Blueprint $table) {
            //
        });
    }
};
