<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('sales_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('sales_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'sales_id'], 'sales_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('sales_translations');
    }
};
