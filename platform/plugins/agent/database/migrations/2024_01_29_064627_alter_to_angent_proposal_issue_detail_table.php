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
        Schema::table('angent_proposal_issue_detail', function (Blueprint $table) {
            $table->integer('quantity_submit')->default(0);
        });
        Schema::table('angent_proposal_issues', function (Blueprint $table) {
            $table->date('expected_date_submit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('angent_proposal_issue_detail', function (Blueprint $table) {
            //
        });
    }
};
