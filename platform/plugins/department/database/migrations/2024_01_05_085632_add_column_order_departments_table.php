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
        Schema::table('order_departments', function (Blueprint $table) {
            $table->timestamp('expected_date')->nullable(); //ngày dự kiến hoàn thành
            $table->timestamp('completion_date')->nullable(); // ngày hoàn thành thực tế
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_departments', function (Blueprint $table) {
            //
        });
    }
};
