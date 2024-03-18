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
        if (Schema::hasTable('agent_products')) {
            Schema::table('agent_products', function (Blueprint $table) {
                if (!Schema::hasColumn('agent_products', 'where_type')) {
                    $table->string('where_type')->default('Botble\Agent\Models\Agent');
                }
                if (!Schema::hasColumn('agent_products', 'where_id')) {
                    $table->bigInteger('where_id')->default(null);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_products', function (Blueprint $table) {
            //
        });
    }
};
