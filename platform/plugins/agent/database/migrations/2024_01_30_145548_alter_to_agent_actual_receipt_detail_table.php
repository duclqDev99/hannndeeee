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
        Schema::table('agent_actual_receipt_detail', function (Blueprint $table) {
            $table->string('color', 60)->nullable();
            $table->string('size', 60)->nullable();
            $table->foreignId('batch_id')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_actual_receipt_detail', function (Blueprint $table) {
            //
        });
    }
};
