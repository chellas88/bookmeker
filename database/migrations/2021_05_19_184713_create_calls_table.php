<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->bigInteger("id")->primary();
            $table->bigInteger("api_call_id");
            $table->string("phone")->nullable();
            $table->mediumText("comment")->nullable();
            $table->integer("hangup_cause")->nullable();
            $table->double("cost")->nullable();
            $table->integer("ivr_digit")->nullable();
            $table->mediumText("ivr_answers")->nullable();
            $table->string("status")->nullable();
            $table->timestamp("started_at")->useCurrent();
            $table->timestamp("answered_at")->useCurrent();
            $table->timestamp("finished_at")->useCurrent();
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
        Schema::dropIfExists('calls');
    }
}
