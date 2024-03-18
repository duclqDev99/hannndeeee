<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('wfp_product_qrcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index();
            $table->string('qr_code', 255);
            $table->string('status', 60)->default('created');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('warehouse_type')->nullable();
            $table->index(['warehouse_id', 'warehouse_type']);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('wfp_product_qrcodes_histories', function (Blueprint $table) {
            $table->id();
            $table->string('action', 120);
            $table->string('description', 255);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->text('extras')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_qrcodes');
    }
};
