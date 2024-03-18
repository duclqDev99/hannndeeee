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
            $table->dropColumn('qty');
            $table->dropColumn('size');
            $table->text('link_design')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retail_order_products', function (Blueprint $table) {
            $table->dropColumn('link_design');
            $table->string('size');
            $table->integer('qty');
        });
    }
};
