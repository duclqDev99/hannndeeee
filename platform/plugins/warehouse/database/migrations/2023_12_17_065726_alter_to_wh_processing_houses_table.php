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
        Schema::table('wh_processing_houses', function (Blueprint $table) {
            $table->string('phone_number',11)->nullable()->change();
            $table->string('address',255)->nullable()->change();
        });
        Schema::table('wh_warehouse', function (Blueprint $table) {
            $table->string('phone_number',11)->nullable()->change();
            $table->string('address',255)->nullable()->change();
        });
        Schema::table('wh_suppliers', function (Blueprint $table) {
            $table->string('phone_number',11)->nullable()->change();
            $table->string('address',255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wh_processing_houses', function (Blueprint $table) {
            //
        });
    }
};
