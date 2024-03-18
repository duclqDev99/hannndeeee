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
        Schema::table('wh_material_proposal_purchase', function(Blueprint $table){
            $table->string('general_order_code',50)->nullable()->after('id');
            $table->foreignId('wh_departure_id')->nullable()->after('warehouse_address');
            $table->string('wh_departure_name', 255)->nullable()->after('wh_departure_id');
            $table->boolean('is_from_supplier')->after('wh_departure_name');
        });

        Schema::table('wh_material_receipt_confirm', function(Blueprint $table){
            $table->string('general_order_code',50)->nullable()->after('id');
            $table->foreignId('wh_departure_id')->nullable()->after('warehouse_address');
            $table->string('wh_departure_name', 255)->nullable()->after('wh_departure_id');
            $table->boolean('is_from_supplier')->after('wh_departure_name');
        });

        Schema::table('wh_material_proposal_purchase_details', function(Blueprint $table){
            $table->integer('supplier_id')->nullable()->after('supplier_name');
        });

        Schema::table('wh_material_receipt_confirm_details', function(Blueprint $table){
            $table->integer('supplier_id')->nullable()->after('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
