<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {


        Schema::create('plc_inventory_discount_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->string('type_warehouse',100);
            $table->string('type_date_active',100);
            $table->integer('time_active');
            $table->string('type_time',100);
            $table->integer('quantity')->nullable();
            $table->integer('quantity_done')->default(0);
            $table->string('type_option',100);
            $table->string('discount_on',100)->nullable();
            $table->bigInteger('value')->nullable();
            $table->string('status', 60)->default('active');
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('plc_inventory_discount_policies');
    }
};
