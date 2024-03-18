<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if(!Schema::hasTable('hd_customer_book_order')){
            Schema::create('hd_customer_book_order', function (Blueprint $table) {
                $table->id();
                $table->string('firstname', 60);
                $table->string('lastname', 60);
                $table->string('email', 191);
                $table->string('phone', 40);
                $table->string('address', 191)->nullable();
                $table->string('type_order')->default('uniform');
                $table->text('note', 191)->nullable();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_customer_book_order');
    }
};
