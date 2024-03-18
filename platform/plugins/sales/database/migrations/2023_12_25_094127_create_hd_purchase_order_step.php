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
        Schema::create('hd_purchase_order_step', function (Blueprint $table) {
            $table->id();
            $table->string('order_code');
            $table->string('step_code');
            $table->string('department_code');
            $table->string('status', 60)->default('processing');
            $table->timestamps();
        });

        Schema::table('hd_orders', function (Blueprint $table) {
            $table->string('order_code')->unique()->change();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_purchase_order_step');
    }
};
