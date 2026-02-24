<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id(); // bigint unsigned auto increment

            $table->string('name');
            $table->string('email')->unique();

            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->rememberToken(); // varchar(100) nullable

            $table->timestamp('created_at')->nullable();
            $table->integer('created_by')->nullable();

            $table->timestamp('updated_at')->nullable();
            $table->integer('updated_by')->nullable();

            $table->timestamp('deleted_at')->nullable(); 
            // OR use: $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};