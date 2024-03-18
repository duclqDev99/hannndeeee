<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wh_actual_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->string('invoice_issuer_name', 255)->nullable();
            $table->string('invoice_confirm_name', 255)->nullable();
            $table->string('proposal_code', 50);
            $table->string('document_number', 255)->nullable();
            $table->string('warehouse_name', 255);
            $table->string('warehouse_address', 255);
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->unsignedDecimal('total_amount', 20);
            $table->decimal('tax_amount', 15)->nullable();
            $table->text('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expected_date')->nullable();
            $table->date('date_confirm')->nullable();
            $table->string('status', 60)->default('pending'); //approved, denied, pending
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('actual_receipts');
        Schema::dropIfExists('actual_receipts_translations');
    }
};
