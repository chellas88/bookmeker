<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZvonobotRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zvonobot_records', function (Blueprint $table) {
            $table->integer("id")->primary();
            $table->mediumText("name");
            $table->dateTime("created_at")->useCurrent();
            $table->dateTime("updated_at")->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zvonobot_records');
    }
}
