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
        Schema::table('retail_orders', function (Blueprint $table) {
            $table->foreignId('created_by_id')->nullable();
        });

        Schema::table('retail_quotations', function (Blueprint $table) {
            $table->foreignId('created_by_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retail_quotations', function (Blueprint $table) {
            $table->dropColumn('created_by_id');
        });

        Schema::table('retail_orders', function (Blueprint $table) {
            $table->dropColumn('created_by_id');
        });
    }
};
