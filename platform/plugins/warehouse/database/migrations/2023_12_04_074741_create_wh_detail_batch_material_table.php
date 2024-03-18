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
        Schema::create('wh_detail_batch_material', function (Blueprint $table) {
            $table->id();
            $table->string('actual_out_detail_id');
            $table->string('batch_code');
            $table->integer('quantity');
            $table->integer('quantity_actual')->nullable();
            $table->string('reason',255)->nullable();

        });
        Schema::table('wh_actual_out_details', function (Blueprint $table) {
            $table->dropColumn('batch_code');
            $table->dropColumn('quantity');
            $table->dropColumn('quantity_actual')->nullable();
            $table->dropColumn('reason')->nullabel();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_detail_batch_material');
    }

};
