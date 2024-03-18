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
        Schema::table('hb_actual_receipt_qrcode', function (Blueprint $table) {
            $table->renameColumn('is_batch', 'batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_actual_receipt_qrcode', function (Blueprint $table) {
            //
        });
    }
};
