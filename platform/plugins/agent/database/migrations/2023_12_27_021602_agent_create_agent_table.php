<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
        Schema::create('agent_warehouse', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('agent_id');
            $table->string('name', 255);
            $table->string('address', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
