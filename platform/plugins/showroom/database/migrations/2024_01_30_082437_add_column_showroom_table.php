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
                if (!Schema::hasColumn('showroom_orders', 'list_id_product_qrcode')) {
                    $table->text('list_id_product_qrcode')->after('where_id');
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
