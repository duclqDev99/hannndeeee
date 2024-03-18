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
            $table->string('unit', 60)->nullable()->after('name');
            $table->foreignId('design_file_id')->nullable()->after('unit');
        });

        Schema::table('hd_orders', function(Blueprint $table){
            $table->string('delivery_location', 255)->nullable()->after('document_number');
            $table->string('delivery_method', 60)->nullable()->after('delivery_location');
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
