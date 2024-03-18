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

        Schema::table('wh_material_proposal_out', function (Blueprint $table) {
            $table->string('general_order_code', 50)->nullable()->change();
        });
        Schema::table('wh_material_out_confirm', function (Blueprint $table) {
            $table->string('general_order_code', 50)->nullable()->change();
        });
        Schema::table('wh_material_proposal_out', function (Blueprint $table) {
            $table->string('reason', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wh_material_proposal_out', function (Blueprint $table) {
            //
        });
    }
};
