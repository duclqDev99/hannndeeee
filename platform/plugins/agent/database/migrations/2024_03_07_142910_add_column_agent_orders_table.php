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
                if (!Schema::hasColumn('agent_orders', 'where_id')) {
                    $table->unsignedBigInteger('where_id')->nullable()->after('id');
                }

                if (!Schema::hasColumn('agent_orders', 'where_type')) {
                    $table->string('where_type', 255)->nullable()->after('where_id');
                }

                $table->index(['where_id', 'where_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
