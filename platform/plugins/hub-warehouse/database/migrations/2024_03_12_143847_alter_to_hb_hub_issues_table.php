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
        Schema::table('hb_hub_issues', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->change();
            $table->string('warehouse_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_hub_issues', function (Blueprint $table) {
            //
        });
    }
};
