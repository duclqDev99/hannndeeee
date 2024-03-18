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
        Schema::create('hd_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('sku', 120);
            $table->double('price');
            $table->string('status', 60)->nullable()->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_products');
    }
};
