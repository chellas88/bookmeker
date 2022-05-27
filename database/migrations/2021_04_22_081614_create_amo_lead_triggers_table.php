<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoLeadTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_lead_triggers', function (Blueprint $table) {
            $table->integer("id")->unique();
            $table->mediumText("name")->nullable();
            $table->integer("status_id")->nullable();
            $table->integer("old_status_id")->nullable();
            $table->integer("price")->nullable();
            $table->integer("responsible_user_id")->nullable();
            $table->timestamp("last_modified")->nullable();
            $table->integer("modified_user_id")->nullable();
            $table->integer("created_user_id")->nullable();
            $table->timestamp("date_create")->nullable();
            $table->integer("pipeline_id")->nullable();
            $table->integer("account_id")->nullable();
            $table->timestamp("created_at")->nullable();
            $table->timestamp("updated_at")->nullable();
            $table->mediumText("short_url")->nullable();
            $table->json("custom_fields")->nullable();
            $table->boolean("is_deleted")->nullable();
            $table->string("last_event")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_lead_triggers');
    }
}
