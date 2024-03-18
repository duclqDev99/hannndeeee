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
        Schema::table('agent_proposal_receipts', function (Blueprint $table) {
            $table->renameColumn('hub_name','warehouse_name');
            $table->renameColumn('hub_address','warehouse_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_proposal_receipts', function (Blueprint $table) {
            //
        });
    }
};
