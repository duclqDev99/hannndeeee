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
        if (Schema::hasTable('agent_proposal_receipt_detail')) {
            Schema::table('agent_proposal_receipt_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('agent_proposal_receipt_detail', 'quantity_submit')) {
                    $table->integer('quantity_submit')->unsigned()->default(0)->after('quantity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_proposal_receipt_detail', function (Blueprint $table) {
            //
        });
    }
};
