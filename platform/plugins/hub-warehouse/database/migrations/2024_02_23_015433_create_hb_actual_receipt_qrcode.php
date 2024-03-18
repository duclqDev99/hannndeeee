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
        Schema::create('hb_actual_receipt_qrcode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->foreignId('product_id');
            $table->foreignId('qrcode_id');
            $table->boolean('is_batch')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hb_actual_receipt_qrcode');
    }
};
