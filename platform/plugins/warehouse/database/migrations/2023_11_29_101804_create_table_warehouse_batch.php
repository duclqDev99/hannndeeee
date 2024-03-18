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
        Schema::create('wh_material_batchs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id');
            $table->string('batch_code');
            $table->foreignId('material_id');
            $table->integer('quantity');
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_material_batchs');
    }
};
