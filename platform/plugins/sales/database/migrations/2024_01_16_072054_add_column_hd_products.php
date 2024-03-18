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
        Schema::table('hd_products', function(Blueprint $table){
            $table->string('color', 60)->nullable();
            $table->string('size', 60)->nullable();
            $table->string('ingredient', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->text('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
