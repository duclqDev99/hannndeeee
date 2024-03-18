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
        Schema::table('wh_actual_out_details', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('quantity_actual');
        });
        Schema::table('wh_actual_outs', function (Blueprint $table) {
            $table->string('warehouse_id')->after('id');
            $table->string('warehouse_name')->after('warehouse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wh_actual_out_details', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
        Schema::table('wh_actual_outs', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
            $table->dropColumn('warehouse_name');
        });

    }
};
