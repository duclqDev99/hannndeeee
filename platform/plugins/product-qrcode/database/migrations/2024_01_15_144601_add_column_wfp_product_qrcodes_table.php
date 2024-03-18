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
            // Bảng tồn tại, tiếp tục kiểm tra cột
            if (!Schema::hasColumn('wfp_product_qrcodes', 'product_type')) {
                // Cột 'quantity_not_qrcode' không tồn tại, tiến hành tạo cột
                Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
                    $table->string('product_type', 255)->default('Botble\Ecommerce\Models\Product');
                });
            }
        }

        Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
            //
        });
    }
};
