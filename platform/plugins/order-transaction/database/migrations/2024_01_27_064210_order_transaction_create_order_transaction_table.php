<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('transaction_code')->unique();
            $table->decimal('total_amount', 12, 2)->unsigned();
            $table->string('status', 60)->default('pending');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('order_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_transaction_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('amount', 12, 2)->unsigned();
            $table->timestamps();

            $table->foreign('order_transaction_id')->references('id')->on('order_transactions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_transactions');
    }
};
