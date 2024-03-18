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
        Schema::create('wh_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('phone_number',11)->nullable();
            $table->string('address',255)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wh_suppliers');

    }
};
