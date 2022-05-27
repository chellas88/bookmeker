<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callbacks', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("triger_type");
            $table->integer("triger_id");
            $table->string("triger_name");
            $table->string("callback_event");
            $table->integer("callback_pipeline")->nullable();
            $table->string("callback_pipeline_name")->nullable();
            $table->integer("callback_status")->nullable();
            $table->string("callback_status_name")->nullable();
            $table->text("callback_note")->nullable();
            $table->integer("callback_task")->nullable();
            $table->text("callback_task_text")->nullable();
            $table->text("callback_url")->nullable();
            $table->boolean("is_active");
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
        Schema::dropIfExists('callbacks');
    }
}
