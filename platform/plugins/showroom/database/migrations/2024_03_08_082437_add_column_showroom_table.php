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
        if (Schema::hasTable('showroom_orders')) {
            Schema::table('showroom_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('showroom_orders', 'list_id_product_qrcode_sale')) {
                    $table->text('list_id_product_qrcode_sale')->after('list_id_product_qrcode')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showroom_orders', function (Blueprint $table) {
            //
        });
    }
};
