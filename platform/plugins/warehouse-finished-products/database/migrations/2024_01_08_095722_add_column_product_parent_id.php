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
        Schema::table('wfp_product_batchs', function(Blueprint $table){
            $table->foreignId('product_parent_id');
        });

        Schema::table('wfp_proposal_receipt_products', function(Blueprint $table){
            $table->bigInteger('print_qrcode_id')->nullable();    
        });

        Schema::table('wfp_receipt_products', function(Blueprint $table){
            $table->bigInteger('print_qrcode_id')->nullable();    
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
