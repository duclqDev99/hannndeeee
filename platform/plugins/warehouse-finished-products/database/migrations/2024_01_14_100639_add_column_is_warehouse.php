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
        Schema::table('wfp_product_issue', function(Blueprint $table){
            $table->boolean('is_warehouse');
        });
        Schema::table('wfp_receipt_products', function(Blueprint $table){
            $table->boolean('from_product_issue')->default(0);
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
