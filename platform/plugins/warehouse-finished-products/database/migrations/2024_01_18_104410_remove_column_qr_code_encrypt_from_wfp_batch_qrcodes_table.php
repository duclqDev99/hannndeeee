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
        if (Schema::hasTable('wfp_batch_qrcodes')) {
            Schema::table('wfp_batch_qrcodes', function (Blueprint $table) {
                if (Schema::hasColumn('wfp_batch_qrcodes', 'qr_code_encrypt')) {
                    $table->dropColumn('qr_code_encrypt');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_batch_qrcodes', function (Blueprint $table) {
            //
        });
    }
};
