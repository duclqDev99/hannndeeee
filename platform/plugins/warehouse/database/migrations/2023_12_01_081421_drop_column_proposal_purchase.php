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
        Schema::table('wh_material_proposal_purchase_details', function($table) {
            $table->dropColumn('is_old_material');
        });
        Schema::table('wh_material_receipt_confirm_details', function($table) {
            $table->dropColumn('is_old_material');
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
