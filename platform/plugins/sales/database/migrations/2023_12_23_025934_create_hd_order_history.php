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
        Schema::create('hd_order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('procedure_code_previous', 60);
            $table->string('procedure_name_previous', 191);
            $table->string('procedure_code_current', 60);
            $table->string('procedure_name_current', 191);
            $table->foreignId('created_by');
            $table->string('created_by_name', 191)->nullable();
            $table->string('status', 60)->default('processing');
            $table->timestamps();
        });

        //Các phần đính kèm đơn đặt hàng như phần thiết kế, rập, ...
        Schema::create('hd_order_attachs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('attach_type', 250);
            $table->foreignId('attach_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_order_history');
        Schema::dropIfExists('hd_order_attachs');
    }
};
