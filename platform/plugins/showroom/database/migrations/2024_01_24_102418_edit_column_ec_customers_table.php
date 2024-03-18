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
        if (Schema::hasTable('ec_customers')) {
            Schema::table('ec_customers', function (Blueprint $table) {
                if (Schema::hasColumn('ec_customers', 'email')) {
                    $table->string('email')->nullable()->change();
                }
                if (Schema::hasColumn('ec_customers', 'password')) {
                    $table->string('password')->nullable()->change();
                }
                if (Schema::hasColumn('ec_customers', 'phone')) {
                    $table->string('phone')->default(0)->change();
                }
            });
        }
//
//        Schema::table('ec_customers', function (Blueprint $table) {
//            //
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            //
        });
    }
};
