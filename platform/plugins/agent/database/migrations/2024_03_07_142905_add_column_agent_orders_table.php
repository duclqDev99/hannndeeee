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
                if (!Schema::hasColumn('agent_orders', 'code')) {
                    $table->string('code', 20)->nullable()->index()->after('id');
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
        });
    }
};
