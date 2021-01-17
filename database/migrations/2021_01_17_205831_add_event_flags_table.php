<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('color')->nullable(false);
        });

        Schema::create('event_flags', function (Blueprint $table) {
            $table->bigInteger('event_id')->nullable(false)->index();
            $table->bigInteger('flag_id')->nullable(false)->index();
            $table->foreign('event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
            $table->foreign('flag_id')
                ->references('id')->on('flags')
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
        Schema::dropIfExists('event_flag');
        Schema::dropIfExists('club');
    }
}
