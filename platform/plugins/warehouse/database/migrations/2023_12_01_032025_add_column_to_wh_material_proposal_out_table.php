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
            $table->foreignId('warehouse_out_id')->after('warehouse_id');
            $table->string('warehouse_type')->after('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wh_material_proposal_out', function (Blueprint $table) {
            $table->dropColumn('warehouse_type');
            $table->dropColumn('warehouse_out_id');
        });
    }
};
