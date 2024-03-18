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
        Schema::table('showroom_exchange_goods', function (Blueprint $table){
            $table->text('description')->nullable();
        });

        Schema::table('showroom_exchange_goods_detail', function (Blueprint $table){
            $table->integer('price_product_change')->nullable()->change();
            $table->integer('price_product_pay')->nullable()->change();
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
