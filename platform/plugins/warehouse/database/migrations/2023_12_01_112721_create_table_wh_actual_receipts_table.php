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
        Schema::create('wh_actual_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('confirm_out_id');
            $table->string('invoice_issuer_name', 255);
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending'); //approved, denied, pending
            $table->timestamps();
        });
        Schema::create('wh_actual_out_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_out_id');
            $table->string('batch_code');
            $table->string('material_code');
            $table->string('material_name');
            $table->string('material_unit',255)->nullable();
            $table->integer('quantity');
            $table->integer('quantity_actual')->nullable();
            $table->integer('material_price')->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_actual_outs');
        Schema::dropIfExists('wh_actual_out_details');
    }
};
