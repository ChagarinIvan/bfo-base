<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteCupGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('cup_groups');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('cup_groups', function (Blueprint $table) {
            $table->bigInteger('cup_id')->unsigned()->nullable(false)->index();
            $table->bigInteger('group_id')->unsigned()->nullable(false)->index();
            $table->foreign('cup_id')
                ->references('id')->on('cups')
                ->onDelete('cascade');
            $table->foreign('group_id')
                ->references('id')->on('groups')
                ->onDelete('cascade');
        });
    }
}
