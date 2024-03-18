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
        if (!Schema::hasTable('agent_statistics')) {
            Schema::create('agent_statistics', function (Blueprint $table) {
                $table->id();
                $table->integer('revenue')->default(0)->unsigned();
                $table->integer('quantity_product')->default(0)->unsigned();
                $table->morphs('where');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_statistics');
    }
};
