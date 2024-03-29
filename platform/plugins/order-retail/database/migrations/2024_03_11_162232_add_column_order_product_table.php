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
        Schema::table('retail_order_products', function (Blueprint $table) {
            $table->string('ingredient')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retail_order_products', function (Blueprint $table) {
            $table->dropColumn('ingredient');
        });
    }
};
