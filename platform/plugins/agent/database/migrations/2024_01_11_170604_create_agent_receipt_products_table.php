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
        Schema::create('agent_receipt_products', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_receipt_products');
    }
};
