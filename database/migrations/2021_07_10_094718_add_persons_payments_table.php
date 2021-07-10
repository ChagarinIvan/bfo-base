<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons_payments', function (Blueprint $table) {
            $table->bigInteger('person_id')->unsigned()->nullable(false)->index();
            $table->integer('year')->nullable(false)->index();
            $table->date('date')->nullable(false);
            $table->foreign('person_id', 'fk_person_id')
                ->references('id')->on('person')
                ->onDelete('cascade');
            $table->unique(['person_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('persons_payments');
    }
}
