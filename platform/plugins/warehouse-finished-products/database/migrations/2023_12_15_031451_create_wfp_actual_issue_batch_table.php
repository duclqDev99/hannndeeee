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
        Schema::create('wfp_actual_issue_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_issue_detail_id');
            $table->foreignId('batch_id');
            $table->string('batch_code',255);
            $table->integer('quantity');
            $table->string('reason',255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wfp_actual_issue_batch');
    }
};
