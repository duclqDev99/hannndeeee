<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('showroom_batch_qrcodes')) {
            Schema::create('showroom_batch_qrcodes', function (Blueprint $table) {
                $table->id();
                $table->string('qr_code',255)->unique();
                $table->foreignId('batch_id')->index();
                $table->string('status',60)->default('in_stock');
                $table->foreignId('warehouse_id');
                $table->string('warehouse_type', 191);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_customers')) {
            Schema::create('showroom_customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');
                $table->morphs('where');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_orders')) {
            Schema::create('showroom_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->morphs('where');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_products')) {
            Schema::create('showroom_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('warehouse_id');
                $table->unsignedInteger('product_id');
                $table->integer('quantity_qrcode')->unsigned()->nullable();
                $table->integer('quantity_not_qrcode')->unsigned()->nullable();
                $table->integer('quantity_sold_not_qrcode')->unsigned()->nullable();
                $table->morphs('where');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_product_batchs')) {
            Schema::create('showroom_product_batchs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receipt_id');
                $table->string('batch_code')->unique();
                $table->integer('quantity');
                $table->integer('start_qty');
                $table->string('status', 60)->default('published');
                $table->foreignId('warehouse_id');
                $table->string('warehouse_type', 255);
                $table->foreignId('product_parent_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_product_batch_detail')) {
            Schema::create('showroom_product_batch_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id');
                $table->foreignId('product_id');
                $table->foreignId('qrcode');
                $table->string('product_name')->nullable();
                $table->string('sku')->nullable();
            });
        }

        if (!Schema::hasTable('showroom_receipt_products')) {
            Schema::create('showroom_receipt_products', function (Blueprint $table) {
                $table->id();
                $table->string('general_order_code', 50)->nullable();
                $table->foreignId('proposal_id');
                $table->foreignId('warehouse_id');
                $table->string('warehouse_name', 255);
                $table->string('warehouse_address', 255);
                $table->foreignId('isser_id');
                $table->string('invoice_issuer_name', 255);
                $table->string('invoice_confirm_name',255)->nullable();
                $table->foreignId('wh_departure_id')->nullable();
                $table->string('wh_departure_name', 255)->nullable();
                $table->tinyInteger('is_warehouse')->default(1);
                $table->integer('quantity');
                $table->text('title')->nullable();
                $table->text('description')->nullable();
                $table->date('expected_date')->nullable();
                $table->date('date_confirm')->nullable();
                $table->string('status', 60)->default('pending');
                $table->bigInteger('print_qrcode_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_receipt_products_detail')) {
            Schema::create('showroom_receipt_products_detail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receipt_id');
                $table->foreignId('product_id');
                $table->foreignId('processing_house_id')->nullable();
                $table->string('processing_house_name')->nullable();
                $table->string('product_name');
                $table->string('sku');
                $table->float('price')->default(0);
                $table->integer('quantity');
                $table->string('color', 25)->nullable();
                $table->string('size', 25)->nullable();
            });
        }


        if (!Schema::hasTable('showrooms')) {
            Schema::create('showrooms', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('description', 255)->nullable();
                $table->string('address', 255)->nullable();
                $table->string('phone_number', 20)->nullable();
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_warehouse')) {
            Schema::create('showroom_warehouse', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('showroom_id');
                $table->string('name', 255);
                $table->string('address', 255)->nullable();
                $table->string('description', 255)->nullable();
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('showroom_user')) {
            Schema::create('showroom_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('showroom_id');
                $table->unsignedInteger('user_id');
                $table->timestamps();
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('showrooms');
    }
};
