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
        if (Schema::hasTable('hb_issue_input_tour')) {
            Schema::table('hb_issue_input_tour', function (Blueprint $table) {
                if (Schema::hasColumn('hb_issue_input_tour', 'hub_proposal_issues_id')) {
                    $table->renameColumn('hub_proposal_issues_id', 'proposal_issues_id');
                }
                if (!Schema::hasColumn('hb_issue_input_tour', 'proposal_issues_id')) {
                    $table->index('proposal_issues_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_issue_input_tour', function (Blueprint $table) {
            //
        });
    }
};
