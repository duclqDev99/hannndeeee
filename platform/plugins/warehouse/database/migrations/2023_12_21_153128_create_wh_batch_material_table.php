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
        Schema::create('wh_batch_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confirm_detail_id');
            $table->foreignId('material_id');
            $table->string('material_code',255);
            $table->integer('quantity');
            $table->string('reason',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_batch_material');
    }
};
