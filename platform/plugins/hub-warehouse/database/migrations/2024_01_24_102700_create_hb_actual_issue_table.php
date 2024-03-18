<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hb_actual_issue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hub_issue_id');
            $table->timestamps();
        });
        Schema::create('hb_actual_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->bigInteger('price')->default(0);
            $table->integer('quantity');
            $table->string('reasoon', 255)->nullable();
            $table->string('qrcode_id', 255)->nullable();
            $table->tinyInteger('is_batch')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hb_actual_issue');
    }
};
