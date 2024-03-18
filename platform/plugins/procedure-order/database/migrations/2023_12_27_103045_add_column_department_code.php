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
        Schema::table('procedure_orders', function(Blueprint $table) {
            if(Schema::hasColumn('procedure_orders', 'roles_join')){
                $table->dropColumn('roles_join');
            }

            if(!Schema::hasColumn('procedure_orders', 'department_code')){
                $table->string('department_code', 191);
            }
        });

        
        if(!Schema::hasColumn('hd_order_history', 'description')){
            Schema::table('hd_order_history', function(Blueprint $table){
                $table->string('description', 250)->nullable()->after('status');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_orders', function (Blueprint $table) {
            if(!Schema::hasColumn('procedure_orders', 'roles_join')){
                $table->text('roles_join')->length(255);
            }
    
            if(Schema::hasColumn('procedure_orders', 'department_code')){
                $table->dropColumn('department_code');
            }
        });
      
    }
};
