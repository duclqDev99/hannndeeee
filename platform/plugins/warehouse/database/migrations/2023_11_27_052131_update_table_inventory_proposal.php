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
        if(Schema::hasTable('mt_proposal_purchase'))
        {
            Schema::dropIfExists('mt_proposal_purchase');
        }
        if(Schema::hasTable('inventory_receipt'))
        {
            Schema::dropIfExists('inventory_receipt');
        }
        Schema::create('wh_material_proposal_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('invoice_issuer_name', 255)->nullable();
            $table->string('invoice_confirm_name',255)->nullable();
            $table->string('proposal_code', 50);
            $table->string('document_number', 255)->nullable();
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->decimal('tax_amount', 15)->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending
            $table->timestamps();
        });
        Schema::create('wh_material_proposal_purchase_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id');
            $table->text('supplier_name', 255);
            $table->text('material_code');
            $table->text('material_name');
            $table->text('material_unit');
            $table->text('material_quantity');
            $table->integer('material_price')->nullable();
            $table->boolean('is_old_material')->default(0);
            $table->integer('material_id')->default(0);
        });

        Schema::create('wh_material_receipt_confirm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('invoice_issuer_name', 255)->nullable();
            $table->string('invoice_confirm_name',255)->nullable();
            $table->foreignId('proposal_id');
            $table->string('document_number', 255)->nullable();
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->integer('quantity');
            $table->unsignedDecimal('total_amount', 20);
            $table->decimal('tax_amount', 15)->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending');//approved, denied, pending
            $table->timestamps();
        });
        Schema::create('wh_material_receipt_confirm_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id');
            $table->text('supplier_name', 255);
            $table->text('material_code');
            $table->text('material_name');
            $table->text('material_unit');
            $table->text('material_quantity');
            $table->integer('material_price')->nullable();
            $table->boolean('is_old_material')->default(0);
            $table->integer('material_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_material_proposal_purchase');
        Schema::dropIfExists('wh_material_proposal_purchase_details');
        Schema::dropIfExists('wh_material_receipt_purchase');
        Schema::dropIfExists('wh_material_receipt_purchase_details');
    }
};
