<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('username', 50)->unique()->nullable();
            $table->string('email', 100)->unique();            
            $table->string('password', 255);
            $table->string('full_name', 100)->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->text('address')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user'); // Chỉ nhận 'user' hoặc 'admin'
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
