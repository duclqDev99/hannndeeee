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
        Schema::table('procedure_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('procedure_orders', 'department_joins')) //check the column
            {
                $table->after('code', function($table){
                    $table->text('department_joins')->nullable();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('procedure_orders', 'department_joins')){
            Schema::table('procedure_orders', function (Blueprint $table) {
                $table->dropColumn('department_joins');
            });
        }
    }
};
