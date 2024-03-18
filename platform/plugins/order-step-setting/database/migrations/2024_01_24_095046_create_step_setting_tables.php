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
        Schema::create('hd_step_setting', function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(255)->nullable();
            $table->integer('index')->length(5);
            $table->boolean('is_init')->default(false);
            $table->timestamps();
        });
        Schema::create('hd_action_setting', function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(255)->nullable();
            $table->string('action_code')->nullable();
            $table->integer('step_index')->length(5);
            $table->string('department_code')->length(50);
            $table->string('valid_status')->length(25)->nullable();
            $table->text('update_relate_actions')->nullable();
            $table->boolean('is_show')->default(false);
            $table->string('action_type')->length(25)->nullable();
            $table->timestamps();
        });
        Schema::create('hd_step', function (Blueprint $table) {
            $table->id();
            $table->integer('step_index')->length(5);
            $table->foreignId('order_id');
            $table->boolean('is_ready')->default(false);
            $table->timestamps();
        });
        Schema::create('hd_action', function (Blueprint $table) {
            $table->id();
            $table->string('note')->length(500)->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->string('status')->length(50)->nullable();
            $table->foreignId('handler_id')->nullable();
            $table->foreignId('step_id');
            $table->string('action_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_step_settings');
        Schema::dropIfExists('hd_action_settings');
        Schema::dropIfExists('hd_order_steps');
        Schema::dropIfExists('hd_order_step_actions');
    }
};