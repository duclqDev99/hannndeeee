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
        Schema::create('agent_product_batchs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->string('batch_code')->unique();
            $table->integer('quantity');
            $table->integer('start_qty');
            $table->string('status', 60)->default('published');
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 255);
            $table->foreignId('product_parent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_product_batchs');
    }
};
