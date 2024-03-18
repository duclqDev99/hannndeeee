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
        Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
            $table->bigInteger('price')->change();
        });
        Schema::table('wfp_actual_issue_detail', function (Blueprint $table) {
            $table->bigInteger('price')->change();
        });
        Schema::table('wfp_proposal_product_issue_detail', function (Blueprint $table) {
            $table->bigInteger('quantityExamine')->nullable();
            $table->bigInteger('price')->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_product_issue_detail', function (Blueprint $table) {
            //
        });
    }
};
