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
        Schema::create('hd_order_reference_procedure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->string('procedure_code', 60);
        });

        Schema::table('hd_orders', function (Blueprint $table) {
            $table->dropColumn('procedure_code');   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_order_reference_procedure');
    }
};
