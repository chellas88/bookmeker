<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_actions', function (Blueprint $table) {
            $table->id();
            $table->string("type");
            $table->integer("sender_id")->nullable();
            $table->string("sender_name")->nullable();
            $table->boolean("is_sms");
            $table->text("sms_text")->nullable();
            $table->text("sms_url")->nullable();
            $table->boolean("is_audio");
            $table->integer("digit")->nullable();
            $table->integer("audio")->nullable();
            $table->integer("sec_audio")->nullable();
            $table->string("destination")->nullable();
            $table->json("leads")->nullable();
            $table->json("filters")->nullable();
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
        Schema::dropIfExists('global_actions');
    }
}
