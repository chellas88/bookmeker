<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_users', function (Blueprint $table) {
          $table->id();
          $table->string('user_id')->unique();
          $table->string('username')->nullable();
          $table->string('platform');
          $table->string('first_name')->nullable();
          $table->string('last_name')->nullable();
          $table->string('phone')->nullable();
          $table->string('amo_id')->nullable();
          $table->boolean('is_amo')->nullable();
          $table->string('status')->nullable();
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
        Schema::dropIfExists('bot_users');
    }
}
