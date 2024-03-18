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
        Schema::create('hd_order_productions', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('customer_name', 50);
            $table->string('email', 255);
            $table->string('phone', 25);
            $table->date('effective_date');
            $table->date('pay_due_date');
            $table->boolean('is_paid')->default(false);
            $table->foreignId('order_id');
            $table->foreignId('created_by_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_order_productions');
    }
};
