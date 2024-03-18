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
        Schema::table('wfp_product_batchs', function(Blueprint $table){
            $table->dropColumn('stock_id');
            $table->foreignId('warehouse_id');
            $table->string('warehouse_type', 255)->after('warehouse_id');
        });

        Schema::table('wfp_actual_receipt_detail', function(Blueprint $table){
            $table->foreignId('batch_id')->after('actual_id');
            $table->dropColumn('processing_house_id');
            $table->dropColumn('processing_house_name');
        });

        // Schema::create('hd_media', function(Blueprint $table){
        //     $table->id();
        //     $table->foreignId('user_id');
        //     $table->string('url', 255);
        //     $table->string('alt', 255)->nullable();
        //     $table->string('action_type');
        //     $table->foreignId('folder_id');
        //     $table->string('mime_type',191);
        //     $table->integer('size');
        //     $table->text('options')->nullable();
        //     $table->timestamps();
        //     $table->timestamp('deleted_at')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wh_medias');
    }
};
