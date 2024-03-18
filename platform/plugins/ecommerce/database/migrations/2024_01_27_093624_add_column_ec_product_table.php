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
                if (!Schema::hasColumn('ec_products', 'item_diameter')) {
                    $table->string('item_diameter')->nullable()->after('weight');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
