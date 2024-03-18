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
        Schema::create('wfp_actual_receipt_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('batch_id');
            $table->integer('quantity');
            $table->integer('start_qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_actual_receipt_batch');
    }
};
