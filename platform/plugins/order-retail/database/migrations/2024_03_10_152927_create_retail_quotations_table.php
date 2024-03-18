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
        Schema::create('retail_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(500);
            $table->decimal('amount', 15)->default(0);
            $table->date('start_date');
            $table->date('due_date');
            $table->decimal('shipping_amount')->nullable();
            $table->string('note')->length(500)->nullable();
            $table->string('order_code')->length(50);
            $table->string('status', 120)->default('pending');
            $table->timestamps();
        });

        Schema::create('retail_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->index();
            $table->string('url', 400)->nullable();
            $table->mediumText('extras')->nullable(); // file name, size, mime_type...
            $table->timestamps();
        });

        Schema::create('retail_payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 120)->default('pending');
            $table->foreignId('quotation_id');
            $table->timestamps();
        });    
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retail_quotations');
        Schema::dropIfExists('retail_contracts');
        Schema::dropIfExists('retail_payments');
    }
};
