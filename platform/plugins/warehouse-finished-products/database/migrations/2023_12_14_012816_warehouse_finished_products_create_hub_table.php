<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('hubs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('phone_number',11)->nullable();
            $table->string('address',255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });
        // Schema::table('wfp_warehouse_finished_products', function (Blueprint $table) {
        //     $table->string('description', 255)->nullable();
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubs');
        Schema::table('wfp_warehouse_finished_products', function (Blueprint $table) {
            $table->dropColumn('description');
        });

    }
};
