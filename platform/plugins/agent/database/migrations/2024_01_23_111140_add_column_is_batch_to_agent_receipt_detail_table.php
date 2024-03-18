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
        Schema::table('agent_receipt_detail', function (Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();
            $table->dropColumn('total_amount');
        });
        Schema::table('agent_proposal_receipt_detail', function (Blueprint $table) {
            $table->bigInteger('price')->nullable()->change();
        });
        Schema::table('agent_receipts', function (Blueprint $table) {
            $table->integer('receipt_code');
            $table->dropColumn('total_amount');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_receipt_detail', function (Blueprint $table) {
            //
        });
    }
};
