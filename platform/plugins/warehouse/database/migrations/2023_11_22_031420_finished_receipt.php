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
        Schema::create('finished_receipt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('proposal_code', 50);
            $table->foreignId('branch_id');
            $table->decimal('amount', 15);
            $table->decimal('tax_amount', 15)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('product_id');
            $table->integer('quantity');
            $table->decimal('price_import',15);
            $table->date('date_import');
            $table->string('status', 60)->default('waiting_stock');//waiting - approved - cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_receipt');
    }
};
