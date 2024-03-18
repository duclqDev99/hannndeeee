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
        if (Schema::hasTable('ec_products')) {
            Schema::table('ec_products', function (Blueprint $table) {
                if (!Schema::hasColumn('ec_products', 'home_display_name')) {
                    $table->string('home_display_name', 255)->nullable()->after('name');
                }
                if (!Schema::hasColumn('ec_products', 'use_home_name')) {
                    $table->tinyInteger('use_home_name')->default(0)->after('name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ec_product', function (Blueprint $table) {
            //
        });
    }
};
