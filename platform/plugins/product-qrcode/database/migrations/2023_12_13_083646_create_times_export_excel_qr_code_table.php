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
        Schema::create('wfp_times_export_product_qrcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index();
            $table->integer('quantity_product');
            $table->integer('times_export');
            $table->string('variation_attributes',100);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_times_export_product_qrcodes');
    }
};
