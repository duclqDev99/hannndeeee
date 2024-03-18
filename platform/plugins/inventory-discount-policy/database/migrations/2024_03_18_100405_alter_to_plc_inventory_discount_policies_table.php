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
        Schema::table('plc_inventory_discount_policies', function (Blueprint $table) {
            $table->string('document',255);
            $table->string('type_date_active',50)->nullable()->change();
            $table->integer('time_active')->nullable()->change();
            $table->string('type_time',50)->nullable()->change();
            $table->string('target',50)->nullable();
            $table->foreignId('product_category_id')->nullable();
            $table->text('image')->nullable();
            $table->string('product', 255)->nullable();
            $table->foreignId('customer_class_type')->nullable();
            $table->string('apply_for',20)->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plc_inventory_discount_policies', function (Blueprint $table) {
            //
        });
        Schema::dropIfExists('plc_products');

    }
};
