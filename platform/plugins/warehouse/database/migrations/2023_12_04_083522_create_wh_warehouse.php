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
        Schema::create('wh_warehouse', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('address',255);
            $table->string('phone_number',11);
            $table->string('status', 60)->default('published');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_warehouse');
    }
};
