<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wh_materials', function (Blueprint $table) {
            DB::table('wh_materials')->where('status', 'published')->update(['status' => 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wh_materials', function (Blueprint $table) {
            //
        });
    }
};
