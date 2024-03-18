<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sw_sale_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_id');
            $table->string('name', 255)->unique();
            $table->string('address', 255)->nullable();
            $table->string('phone', 12)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('status', 60)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sw_sale_warehouses');
    }
};
