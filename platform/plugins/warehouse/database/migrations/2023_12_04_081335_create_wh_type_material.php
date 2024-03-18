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
        Schema::create('wh_type_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique(0);
            $table->foreignId('parent_id')->default(0);
            $table->string('description', 400)->nullable();
            $table->string('status', 60)->default('published');
            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_default')->unsigned()->default(0);
            $table->timestamps();
        });
        Schema::create('wh_material_type', function (Blueprint $table) {
            $table->foreignId('material_id')->index();
            $table->foreignId('type_material_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_type_materials');
        Schema::dropIfExists('wh_material_type');
    }
};
