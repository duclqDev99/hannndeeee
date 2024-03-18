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
        Schema::table('hb_hub_receipt_detail', function (Blueprint $table) {
            $table->string('qrcode_id', 255)->nullable();
            $table->tinyInteger('is_odd')->nullable();
        });
        Schema::table('hb_hub_receipt', function (Blueprint $table) {
            $table->string('receipt_code', 60);
            $table->string('reason')->nullable();
        });
        Schema::table('hb_hub_issues', function (Blueprint $table) {
            $table->string('issue_code', 60);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hb_hub_receipt_detail', function (Blueprint $table) {
            //
        });
    }
};
