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
        Schema::create('agent_batch_qrcodes', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code',255)->unique();
            $table->string('qr_code_encrypt',255);
            $table->foreignId('batch_id')->index();
            $table->string('status',60)->default('in_stock');
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 191);
            $table->text('base_code_64');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_batch_qrcodes');
    }
};
