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
        Schema::create('agent_product_batch_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id');
            $table->foreignId('product_id');
            $table->foreignId('qrcode');
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_product_batch_detail');
    }
};
