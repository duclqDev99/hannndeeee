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
        Schema::table('hd_orders', function (Blueprint $table) {
            if(Schema::hasColumn('hd_orders', 'procedure_code')){
                $table->dropColumn('procedure_code');
            }

            if(!Schema::hasColumn('hd_orders', 'procedure_id')){
                $table->after('status', function($table){
                    $table->foreignId('procedure_id')->nullable();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hd_orders', function (Blueprint $table) {
            if(Schema::hasColumn('hd_orders', 'procedure_id')){
                $table->dropColumn('procedure_id');
            }

            if(!Schema::hasColumn('hd_orders', 'procedure_code')){
                $table->string('procedure_code')->nullable();
            }
        });
    }
};
