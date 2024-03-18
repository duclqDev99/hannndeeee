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
        if (Schema::hasTable('wfp_product_in_stock')) {
            Schema::table('wfp_product_in_stock', function (Blueprint $table) {
                if (!Schema::hasColumn('wfp_product_in_stock', 'quantity_sold_not_qrcode')) {
                    $table->integer('quantity_sold_not_qrcode')->unsigned()->default(0);
                }
            });
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_product_in_stock', function (Blueprint $table) {
            //
        });
    }
};
