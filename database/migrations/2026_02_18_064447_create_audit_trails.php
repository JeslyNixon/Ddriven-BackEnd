<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->dateTime('timestamp')->useCurrent();
            $table->string('user_id', 100)->nullable();
            $table->string('username', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('action', 100)->nullable();
            $table->string('resource', 100)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->integer('status_code')->nullable();
            $table->string('duration', 50)->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('app_name', 100)->nullable();
            $table->string('module_name', 100)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};