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
                if (Schema::hasColumn('agent_orders', 'order_id')) {
                    $table->dropColumn('order_id');
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
