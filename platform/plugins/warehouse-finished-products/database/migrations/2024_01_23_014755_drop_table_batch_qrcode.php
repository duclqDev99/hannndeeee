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
            $table->string('identifier', 10)->nullable()->change();
        });

        Schema::dropIfExists('wfp_batch_qrcodes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
