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
        Schema::create('retail_productions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->length(50)->unique();
            $table->string('order_code')->length(50)->unique();
            $table->foreignId('quotation_id');
            $table->string('status', 120)->default('pending');
            $table->text('note')->nullable();
            $table->foreignId('created_by_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_productions');
    }
};
