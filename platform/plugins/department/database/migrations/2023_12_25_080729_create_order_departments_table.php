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
        Schema::create('order_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('department_code')->length(50);
            $table->foreignId('assignee_id')->nullable();
            $table->string('status', 60)->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_departments');
    }
};
