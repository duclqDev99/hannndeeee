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
        Schema::table('wfp_proposal_receipt_product_detail', function (Blueprint $table) {
            $table->string('color', 25)->nullable();
            $table->string('size', 25)->nullable();
        });
        Schema::table('wfp_receipt_products_detail', function (Blueprint $table) {
            $table->string('color', 25)->nullable();
            $table->string('size', 25)->nullable();
        });
        Schema::table('wfp_product_batchs', function (Blueprint $table) {
            $table->string('batch_code')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
