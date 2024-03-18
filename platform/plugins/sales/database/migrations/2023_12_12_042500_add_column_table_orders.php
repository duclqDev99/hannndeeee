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
        Schema::table('hd_orders', function (Blueprint $table) {
            $table->bigInteger('total_qty')->after('document_number');
            $table->decimal('amount')->after('document_number');
            $table->decimal('tax_amount')->nullable()->after('amount');
            $table->string('coupon_code', 120)->nullable()->after('tax_amount');
            $table->decimal('discount_amount')->nullable()->after('coupon_code');
            $table->string('discount_description', 255)->nullable()->after('discount_amount');
            $table->decimal('sub_total')->after('discount_description');
            $table->string('current_process_status')->default('processing')->after('sub_total');//processing - pause - completed
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
