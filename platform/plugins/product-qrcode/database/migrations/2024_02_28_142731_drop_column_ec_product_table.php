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
        if (Schema::hasTable('wfp_product_qrcodes')) {
            Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
                if (Schema::hasColumn('wfp_product_qrcodes', 'production_time')) {
                    $table->dropColumn('production_time');
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
