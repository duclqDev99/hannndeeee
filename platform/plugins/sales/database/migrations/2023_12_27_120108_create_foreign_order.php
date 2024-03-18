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
        Schema::table('hd_order_reference_procedure', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('hd_orders')->onDelete('cascade');
            $table->foreign('procedure_code')->references('code')->on('procedure_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foreign_order');
    }
};
