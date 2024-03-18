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
        Schema::table('wfp_actual_issue', function (Blueprint $table) {
            $table->text('image')->nullable();

        });
        Schema::table('wfp_actual_issue_detail', function (Blueprint $table) {
            $table->string('color',60)->nullable();
            $table->string('size',60)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_actual_issue', function (Blueprint $table) {
            //
        });
    }
};
