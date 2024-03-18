<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Kiểm tra xem constraint unique có tồn tại không
        $table = 'wfp_times_export_product_qrcodes';
        $index = 'wfp_times_export_product_qrcodes_title_unique';
        if (Schema::hasColumn($table, 'title')) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            if (array_key_exists($index, $indexes)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropUnique('wfp_times_export_product_qrcodes_title_unique');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wfp_times_export_product_qrcodes', function (Blueprint $table) {
            //
        });
    }
};
