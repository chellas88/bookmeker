<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_calls', function (Blueprint $table) {
            $table->bigInteger("id")->primary();
            $table->integer("lead_id")->nullable();
            $table->integer("trigger_id")->nullable();
            $table->string("phone")->nullable();
            $table->string("answer")->nullable();
            $table->string("webhookTry")->nullable();
            $table->string("plannedAt")->nullable();
            $table->integer("needRecording")->nullable();
            $table->integer("webhookSent")->nullable();
            $table->json("send_package")->nullable();
            $table->json("response_package")->nullable();
            $table->json("hook_package")->nullable();
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
        Schema::dropIfExists('api_calls');
    }
}
