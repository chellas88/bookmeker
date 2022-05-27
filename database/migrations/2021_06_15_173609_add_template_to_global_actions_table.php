<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateToGlobalActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_actions', function (Blueprint $table) {
          $table->string("type_message")->nullable();
          $table->string("template_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_actions', function (Blueprint $table) {
          $table->dropColumn(['type_message']);
          $table->dropColumn(['template_id']);
        });
    }
}
