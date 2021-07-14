<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProtocolLineEventIdForeingKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('protocol_lines', function (Blueprint $table) {
            $table->bigInteger('event_id')->unsigned()->nullable(false)->change();
            $table->foreign('event_id', 'fk_event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('protocol_lines', function (Blueprint $table) {
            $table->integer('event_id')->nullable(false)->default('')->change();
            $table->dropForeign('fk_event_id');
        });
    }
}
