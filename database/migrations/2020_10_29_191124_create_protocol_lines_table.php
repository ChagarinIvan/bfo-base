<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProtocolLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('protocol_lines', function (Blueprint $table) {
            $table->id();
            $table->integer('serial_number');
            $table->string('lastname');
            $table->string('firstname');
            $table->string('club');
            $table->integer('year')->nullable(true);
            $table->string('rank')->default('');
            $table->integer('runner_number');
            $table->time('time')->nullable(true);
            $table->integer('place')->nullable(true);
            $table->string('complete_rank')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('protocol_lines');
    }
}
