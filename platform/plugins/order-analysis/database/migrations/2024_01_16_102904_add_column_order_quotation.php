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
        Schema::table('hd_order_quotation', function(Blueprint $table){
            $table->string('title', 255);
            $table->bigInteger('total_amount');
            $table->timestamp('effective_time');
            $table->date('effective_payment');
            $table->bigInteger('transport_costs')->nullable();
            $table->text('description')->nullable();
            $table->dropColumn('price');
            $table->dropColumn('analysis_id');
        });

        Schema::table('hd_order_attachs', function(Blueprint $table){
            $table->string('status', 60)->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
