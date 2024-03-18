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
        Schema::create('hd_steps', function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(255)->nullable();
            $table->boolean('is_important')->default(false);
            $table->boolean('is_ready')->default(false);
            $table->timestamps();
        });

        Schema::create('hd_step_details', function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(255)->nullable();
            $table->string('department_code')->length(50);
            $table->string('status')->length(50);
            $table->foreignId('hd_step_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_steps');
        Schema::dropIfExists('hd_step_details');
    }
};
