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
        if (Schema::hasTable('hb_product_in_stock')) {
            // Bảng tồn tại, tiếp tục kiểm tra cột
            if (!Schema::hasColumn('hb_product_in_stock', 'quantity_sold_not_qrcode')) {
                // Cột 'quantity_not_qrcode' không tồn tại, tiến hành tạo cột
                Schema::table('hb_product_in_stock', function (Blueprint $table) {
                    $table->integer('quantity_sold_not_qrcode')->unsigned()->default(0);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_product_in_stock', function (Blueprint $table) {
            //
        });
    }
};
