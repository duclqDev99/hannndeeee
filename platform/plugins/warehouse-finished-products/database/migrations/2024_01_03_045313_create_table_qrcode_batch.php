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
        Schema::create('wfp_batch_qrcodes', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code',255)->unique();
            $table->string('qr_code_encrypt',255);
            $table->foreignId('batch_id')->index();
            $table->string('status',60)->default('in_stock');
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 191);
            $table->timestamps();
        });

        Schema::table('wfp_actual_receipt', function(Blueprint $table){
            $table->text('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_batch_qrcodes');
    }
};
