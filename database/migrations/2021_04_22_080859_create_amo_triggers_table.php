<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_triggers', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("event");
            $table->string("delay_type");
            $table->bigInteger("delay_time")->nullable();
            $table->integer("pipeline_id");
            $table->string("pipeline_name");
            $table->integer("status_id");
            $table->string("status_name");
            $table->integer("record_id");
            $table->string("record_name");
            $table->integer("sec_record_id")->nullable();
            $table->string("sec_record_name")->nullable();
            $table->boolean("is_sec_record");
            $table->integer('sender_id');
            $table->string("sender_name");
            $table->boolean("send_sms");
            $table->mediumText("sms_text")->nullable();
            $table->mediumText("sms_url")->nullable();
            $table->boolean("is_active");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
            $table->integer("sec_record_digit")->nullable();
            $table->boolean("is_ivr");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_triggers');
    }
}
