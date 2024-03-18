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
        if (Schema::hasColumn('hd_orders', 'procedure_id')) {
            Schema::table('hd_orders', function (Blueprint $table) {
                $table->dropColumn('procedure_id');
            });
        }

        if (!Schema::hasColumn('hd_orders', 'procedure_code')) {
            Schema::table('hd_orders', function (Blueprint $table) {
                $table->string('procedure_code')->length(50);
            });
        }

        if (Schema::hasColumn('procedure_orders', 'department_joins')) {
            Schema::table('procedure_orders', function (Blueprint $table) {
                $table->dropColumn('department_joins');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_orders', function (Blueprint $table) {
            $table->text('department_joins');
        });
        Schema::table('hd_orders', function (Blueprint $table) {
            $table->dropColumn('procedure_code');
        });
        Schema::table('hd_orders', function (Blueprint $table) {
            $table->foreignId('procedure_id');
        });
    }
};
