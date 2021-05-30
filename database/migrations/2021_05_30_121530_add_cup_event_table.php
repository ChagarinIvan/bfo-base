<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCupEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cup_events', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cup_id')->unsigned()->nullable(false)->index();
            $table->bigInteger('event_id')->unsigned()->nullable(false)->index();
            $table->integer('points')->unsigned()->nullable(false);
            $table->timestamps();
            $table->foreign('cup_id')
                ->references('id')->on('cups')
                ->onDelete('cascade');
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cup_events');
    }
}
