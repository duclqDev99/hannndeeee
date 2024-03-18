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
        Schema::create('wfp_product_qrcodes_base64', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_qrcode_id');
            $table->text('base64');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_product_qrcodes_base64', function (Blueprint $table) {
            //
        });
    }
};
