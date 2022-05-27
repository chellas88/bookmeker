<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTrigersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_trigers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event');
            $table->string('delay_type');
            $table->integer('delay_time')->nullable();
            $table->integer('pipeline_id');
            $table->string('pipeline_name');
            $table->integer('status_id');
            $table->string('status_name');
            $table->boolean('is_active');
            $table->text('sms_text');
            $table->integer('sender_id');
            $table->string("sender_name");
            $table->string('sms_url')->nullable();
            $table->boolean("send_sms");
            $table->timestamp("created_at")->useCurrent();
            $table->timestamp("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_trigers');
    }
}
