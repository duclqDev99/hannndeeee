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
        Schema::table('wh_material_proposal_purchase', function(Blueprint $table){
            $table->integer('proposal_out_id')->nullable();
        });
        Schema::table('wh_material_proposal_out', function(Blueprint $table){
            $table->integer('proposal_purchase_id')->nullable();
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
