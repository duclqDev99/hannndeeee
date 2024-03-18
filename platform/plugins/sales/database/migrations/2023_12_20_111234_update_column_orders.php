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
        Schema::table('hd_orders', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('tax_amount')->change();
            $table->bigInteger('discount_amount')->change();
            $table->bigInteger('sub_total')->change();
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
