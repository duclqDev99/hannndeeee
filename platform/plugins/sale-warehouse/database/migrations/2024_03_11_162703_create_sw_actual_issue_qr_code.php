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
        Schema::create('sw_actual_issue_qrcode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id');
            $table->foreignId('product_id');
            $table->foreignId('batch_id')->nullable();
            $table->foreignId('is_batch')->default(0);
            $table->foreignId('qrcode_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sw_actual_issue_qrcode');
    }
};
