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
        if (Schema::hasTable('agent_orders')) {
            Schema::table('agent_orders', function (Blueprint $table) {
                if (Schema::hasColumn('agent_orders', 'where_type')) {
                    $table->dropColumn('where_type');
                }
                if (Schema::hasColumn('agent_orders', 'where_id')) {
                    $table->dropColumn('where_id');
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_orders', function (Blueprint $table) {
            //
        });
    }
};
