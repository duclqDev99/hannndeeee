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
        Schema::create('hd_order_quotation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('analysis_id');
            $table->bigInteger('price')->nullable();
            $table->string('status', 60);
            $table->boolean('is_canceled')->default(false);
            $table->string('reasoon', 220)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_order_quotation');
    }
};
