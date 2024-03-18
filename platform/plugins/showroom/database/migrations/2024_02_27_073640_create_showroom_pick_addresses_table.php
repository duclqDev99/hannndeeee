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
        Schema::create('showroom_pick_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('service_type')->length(50);
            $table->string('showroom_code')->length(50)->nullable();
            $table->string('pick_name')->length(255)->nullable();
            $table->string('pick_email')->nullable();
            $table->string('pick_address_id')->nullable();
            $table->foreignId('province_id')->nullable();
            $table->foreignId('district_id')->nullable();
            $table->foreignId('ward_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_pick_addresses');
    }
};
