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
        Schema::table('wfp_product_qrcodes', function(Blueprint $table){
            $table->string('reference_type');
            $table->renameColumn('product_id', 'reference_id');
            $table->bigInteger('times_product_id')->nullable()->change();
            $table->text('base_code_64')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_product_qrcodes', function (Blueprint $table) {
            $table->renameColumn('reference_id', 'product_id');
        });
    }
};
