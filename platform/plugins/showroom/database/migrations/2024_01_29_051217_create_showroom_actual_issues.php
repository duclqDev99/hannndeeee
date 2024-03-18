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
        Schema::create('showroom_actual_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showroom_issue_id');
            $table->string('image',60)->nullable();
            $table->timestamps();
        });
        Schema::create('showroom_actual_issue_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actual_id');
            $table->foreignId('product_id');
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->bigInteger('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->string('reasoon', 255)->nullable();
            $table->foreignId('qrcode_id')->nullable();
            $table->tinyInteger('is_batch')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showroom_actual_issues');
        Schema::dropIfExists('showroom_actual_issue_detail');
    }
};
