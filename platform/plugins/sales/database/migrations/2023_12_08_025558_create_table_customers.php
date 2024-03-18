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
        Schema::create('hd_customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('gender', 25)->nullable();
            $table->string('email', 255);
            $table->string('phone', 25);
            $table->string('address', 255)->nullable();
            $table->date('dob')->nullable();
            $table->string('level');
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('hd_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50);
            $table->foreignId('id_user');
            $table->string('username', 50);
            $table->string('email', 255);
            $table->string('phone', 25);
            $table->string('invoice_issuer_name', 50);
            $table->string('document_number', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->date('expected_date', 50);
            $table->date('date_confirm', 50)->nullable();
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('hd_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_order');
            $table->string('product_name', 255);
            $table->string('product_size', 50);
            $table->string('product_type', 255);
            $table->integer('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_customers');
        Schema::dropIfExists('hd_orders');
        Schema::dropIfExists('hd_order_details');
    }
};
