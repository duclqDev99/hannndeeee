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
        Schema::table('hb_hub_issues', function (Blueprint $table) {
            $table->tinyInteger('is_batch')->default(1);
        });
        Schema::table('hb_hub_receipt', function (Blueprint $table) {
            $table->tinyInteger('is_batch')->default(1);
        });
        Schema::table('hb_proposal_hub_issues', function (Blueprint $table) {
            $table->tinyInteger('is_batch')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_hub_issues', function (Blueprint $table) {
            //
        });
    }
};
