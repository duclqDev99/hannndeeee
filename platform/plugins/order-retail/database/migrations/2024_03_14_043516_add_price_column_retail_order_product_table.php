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
            $table->decimal('hgf_price', 15)->default(0);
            $table->decimal('quotation_price', 15)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retail_order_products', function (Blueprint $table) {
            $table->dropColumn('hgf_price');
            $table->dropColumn('quotation_price');
        });
    }
};
