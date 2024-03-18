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
        Schema::table('hb_proposal_hub_issue_detail', function (Blueprint $table) {
            $table->dropColumn('attribute');
            $table->tinyInteger('is_batch')->default(0);
            $table->string('color',25)->nullable();
            $table->string('size',25)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_proposal_hub_issue_detail', function (Blueprint $table) {
            //
        });
    }
};
