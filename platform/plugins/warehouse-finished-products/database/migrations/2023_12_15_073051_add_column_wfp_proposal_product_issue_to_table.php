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
            $table->foreignId('proposal_receipt_id')->nullable();
            $table->string('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_proposal_product_issue', function (Blueprint $table) {
            $table->dropColumn('proposal_receipt_id');
            $table->dropColumn('reason');
        });
    }
};
