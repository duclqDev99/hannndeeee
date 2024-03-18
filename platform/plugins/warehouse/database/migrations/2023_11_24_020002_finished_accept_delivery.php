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
        Schema::create('finished_accept_delivery', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_code');
            $table->foreignId('branch_id');
            $table->foreignId('product_id');
            $table->foreignId('agency_id');
            $table->foreignId('user_id');
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->date('expected_date');
            $table->string('status',50)->default('not_stock');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_accept_delivery');
    }
};
