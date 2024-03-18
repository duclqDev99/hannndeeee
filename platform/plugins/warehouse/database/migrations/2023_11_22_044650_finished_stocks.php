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
        Schema::create('finished_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id');
            $table->foreignId('product_id');
            $table->integer('quantity');
            $table->string('status',50)->default('published');
            $table->timestamps();
        });

        Schema::dropIfExists('finished_product');
        Schema::dropIfExists('finished_product_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_stocks');
    }
};
