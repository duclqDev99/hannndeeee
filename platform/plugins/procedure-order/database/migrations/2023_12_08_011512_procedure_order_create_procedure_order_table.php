<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('procedure_orders', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 255)->unique();
            $table->text('roles_join');
            $table->string('parent_id',100);
            $table->text('next_step');
            $table->string('cycle_point',255)->nullable();
            $table->string('main_thread_status', 255)->default("main_branch");
            $table->softDeletes();
            $table->string('created_by',100)->nullable();
            $table->string('updated_by',100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_orders');
    }
};
