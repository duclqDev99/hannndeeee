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
        if(!Schema::hasTable('wh_material_proposal_out'))
        {
            Schema::create('wh_material_proposal_out', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id');
                $table->string('invoice_issuer_name', 255)->nullable();
                $table->string('invoice_confirm_name', 255)->nullable();
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
                $table->string('status', 60)->default('pending'); //approved, denied, pending
                $table->timestamps();
            });

            Schema::create('wh_material_proposal_out_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proposal_id');
                $table->text('material_code');
                $table->text('material_name');
                $table->text('material_unit');
                $table->text('material_quantity');
                $table->integer('material_price')->nullable();
            });
            Schema::create('wh_material_out_confirm', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id');
                $table->string('invoice_issuer_name', 255)->nullable();
                $table->string('invoice_confirm_name', 255)->nullable();
                $table->foreignId('proposal_id');
                $table->string('document_number', 255)->nullable();
                $table->string('warehouse_name', 255);
                $table->string('warehouse_address', 255);
                $table->integer('quantity');
                $table->unsignedDecimal('total_amount', 20);
                $table->text('title')->nullable();
                $table->text('description')->nullable();
                $table->date('expected_date')->nullable();
                $table->date('date_confirm')->nullable();
                $table->string('status', 60)->default('pending'); //approved, denied, pending
                $table->timestamps();
            });
            Schema::create('wh_material_out_confirm_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('out_id');
                $table->text('material_code');
                $table->text('material_name');
                $table->text('material_unit');
                $table->text('material_quantity');
                $table->integer('material_price')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
