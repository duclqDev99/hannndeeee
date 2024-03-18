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
        if (Schema::hasTable('agents')) {
            Schema::table('agents', function (Blueprint $table) {
                if (!Schema::hasColumn('agents', 'discount_value')) {
                    $table->integer('discount_value')->unsigned()->default(0);
                }
                if (!Schema::hasColumn('agents', 'discount_type')) {
                    $table->string('discount_type')->default(NULL); // hoặc %
                }
            });
        }

        Schema::table('agents', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            //
        });
    }
};
