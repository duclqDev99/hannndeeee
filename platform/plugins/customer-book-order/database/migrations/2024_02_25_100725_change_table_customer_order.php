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
        Schema::table('hd_customer_book_order', function(Blueprint $table){
            $table->integer('quantity')->after('note');
            $table->text('image')->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hd_customer_book_order', function(Blueprint $table){
            $table->dropColumn('quantity');
            $table->dropColumn('image');
        });
    }
};
