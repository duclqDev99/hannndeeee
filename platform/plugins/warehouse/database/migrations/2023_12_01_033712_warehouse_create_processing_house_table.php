<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wh_processing_houses', function (Blueprint $table) {
            $table->id();
            $table->string('code',255)->unique();
            $table->string('name', 255);
            $table->string('phone_number',11);
            $table->string('address',255);
            $table->text('description')->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });



    }

    public function down(): void
    {
        Schema::dropIfExists('wh_processing_houses');

    }
};
