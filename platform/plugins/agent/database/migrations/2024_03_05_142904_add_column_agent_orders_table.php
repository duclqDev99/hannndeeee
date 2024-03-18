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
                if (!Schema::hasColumn('agent_orders', 'list_id_product_qrcode')) {
                    $table->text('list_id_product_qrcode')->after('id');
                    $table->decimal('amount', 15, 2)->unsigned()->after('id');
                    $table->string('description', 255)->nullable()->after('id');
                    $table->string('status', 60)->default('pending')->nullable()->after('id');
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
