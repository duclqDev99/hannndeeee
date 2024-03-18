<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('finished_product', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('finished_product_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('warehouses_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'warehouses_id'], 'finished_product_translations_primary');
        });

    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('finished_product');
        Schema::dropIfExists('finished_product_translations');
    }
};
