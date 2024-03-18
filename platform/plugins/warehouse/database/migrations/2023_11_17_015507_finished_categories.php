<?php

use Botble\ACL\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finished_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 25);
            $table->foreignId('parent_id')->default(0);
            $table->string('status', 60)->default('published');
            $table->foreignId('author_id');
            $table->string('author_type', 255)->default(addslashes(User::class));
            $table->tinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('finished_product_categories');
    }
};
