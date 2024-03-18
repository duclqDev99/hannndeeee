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
            // Bảng tồn tại, tiếp tục kiểm tra cột
            Schema::table('wfp_product_in_stock', function (Blueprint $table) {
                $table->timestamps();
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
