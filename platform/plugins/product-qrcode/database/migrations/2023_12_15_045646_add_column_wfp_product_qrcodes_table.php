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
        Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
            $table->unique('qr_code')->index();
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
