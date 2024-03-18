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
        Schema::table('showrooms', function (Blueprint $table) {
            $table->string('code')->length(50)->nullable()->after('name');
            $table->text('lat')->nullable();
            $table->text('lon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showrooms', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->dropColumn('lat');
            $table->dropColumn('lon');
        });
    }
};
