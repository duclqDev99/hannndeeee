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
        if (!Schema::hasTable('hb_issue_input_tour')) {
            Schema::create('hb_issue_input_tour', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hub_proposal_issues_id');
                $table->foreignId('qrcode_id');
                $table->morphs('where');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations. 
     */
    public function down(): void
    {
        Schema::dropIfExists('hb_issue_input_tour');
    }
};
