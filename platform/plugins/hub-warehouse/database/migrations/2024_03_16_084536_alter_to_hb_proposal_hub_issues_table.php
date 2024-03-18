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
        Schema::table('hb_proposal_hub_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('hb_proposal_hub_issues', 'policies_id')) {
                $table->foreignId('policies_id')->nullable();
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_proposal_hub_issues', function (Blueprint $table) {
            //
        });
    }
};
