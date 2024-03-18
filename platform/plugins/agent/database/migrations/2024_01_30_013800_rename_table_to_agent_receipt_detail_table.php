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
        Schema::rename('angent_proposal_issues', 'agent_proposal_issues');
        Schema::rename('angent_proposal_issue_detail', 'agent_proposal_issue_detail');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('agent_proposal_issues', 'angent_proposal_issues');
        Schema::rename('agent_proposal_issue_detail', 'angent_proposal_issue_detail');
    }
};
