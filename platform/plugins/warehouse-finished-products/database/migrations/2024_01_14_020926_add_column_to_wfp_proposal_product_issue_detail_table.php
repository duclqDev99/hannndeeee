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
        Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
            $table->string('color', 25)->nullable();
            $table->string('size', 25)->nullable();
            $table->dropColumn('attribute');
        });
        Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
            $table->string('color', 25)->nullable();
            $table->string('size', 25)->nullable();
            $table->dropColumn('attribute');
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
