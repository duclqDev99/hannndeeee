<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_issues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('agent_issues_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->foreignId('agent_issues_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'agent_issues_id'], 'agent_issues_translations_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_issues');
        Schema::dropIfExists('agent_issues_translations');
    }
};
