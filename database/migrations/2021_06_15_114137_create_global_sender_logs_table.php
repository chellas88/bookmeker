<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalSenderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_sender_logs', function (Blueprint $table) {
            $table->id();
            $table->string("type");
            $table->integer("global_id");
            $table->integer("lead_id");
            $table->integer("contact_id")->nullable();
            $table->string("phone")->nullable();
            $table->string("name")->nullable();
            $table->string("first_name")->nullable();
            $table->string("last_name")->nullable();
            $table->string("email")->nullable();
            $table->json("msg_data")->nullable();
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
        Schema::dropIfExists('global_sender_logs');
    }
}
