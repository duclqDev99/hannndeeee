<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE wfp_proposal_receipt_products MODIFY COLUMN is_warehouse ENUM('warehouse','warehouse-odd','inventory')");
        DB::statement("ALTER TABLE wfp_receipt_products MODIFY COLUMN is_warehouse ENUM('warehouse','warehouse-odd','inventory')");
        DB::statement("ALTER TABLE wfp_actual_receipt MODIFY COLUMN is_warehouse ENUM('warehouse','warehouse-odd','inventory')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
