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
        Schema::table('hb_issue_input_tour', function (Blueprint $table) {
            //
        });
        if (Schema::hasTable('hb_issue_input_tour')) {
            Schema::table('hb_issue_input_tour', function (Blueprint $table) {
                if (!Schema::hasColumn('hb_issue_input_tour', 'product_id')) {
                    $table->foreignId('product_id')->index();
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
