<?php

use Botble\OrderRetail\Enums\ShippingTypeEnum;
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
        Schema::create('retail_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->length(50)->unique();
            $table->string('order_type', 60)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_phone', 25)->nullable();
            $table->date('expected_date');
            $table->string('status', 120)->default('pending');
            $table->integer('total_qty')->default(0);
            $table->decimal('amount', 15)->default(0);
            $table->decimal('tax_amount')->nullable();
            $table->text('note')->nullable();
            $table->string('coupon_code', 120)->nullable();
            $table->decimal('discount_amount', 15)->nullable();
            $table->decimal('sub_total', 15)->nullable();
            $table->string('discount_description', 255)->nullable();
            $table->foreignId('payment_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_parent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('retail_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->date('dob')->nullable();
            $table->string('level');
            $table->timestamps();
        });

        Schema::create('retail_order_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->string('product_name');
            $table->integer('qty');
            $table->decimal('price', 15);
            $table->string('cal');
            $table->string('size');
            $table->text('description')->nullable();
            $table->text('options')->nullable();
            $table->float('weight')->default(0)->nullable();
            $table->string('address')->length(500);
            $table->string('shipping_method', 60)->nullable();
            $table->foreignId('product_id')->nullable();
            $table->foreignId('order_id');
            $table->timestamps();
        });

        Schema::create('retail_product_image_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retail_product_id')->nullable()->index();
            $table->foreignId('product_id')->nullable()->index();
            $table->string('url', 400)->nullable();
            $table->mediumText('extras')->nullable(); // file name, size, mime_type...
            $table->timestamps();
        });

        Schema::create('retail_order_design_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retail_product_id')->nullable()->index();
            $table->string('url', 400)->nullable();
            $table->mediumText('extras')->nullable(); // file name, size, mime_type...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_orders');
        Schema::dropIfExists('retail_customers');
        Schema::dropIfExists('retail_order_products');
        Schema::dropIfExists('retail_product_image_files');
        Schema::dropIfExists('retail_order_design_files');
    }
};
