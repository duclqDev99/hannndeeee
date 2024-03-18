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
                if (!Schema::hasColumn('ec_products', 'origin_source')) {
                    $table->string('origin_source')->nullable()->after('sku');
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
