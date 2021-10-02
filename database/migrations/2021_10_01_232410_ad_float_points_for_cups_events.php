<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class AdFloatPointsForCupsEvents extends Migration
{
    private Builder $builder;

    public function __construct()
    {
        $this->builder = app(Builder::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->builder->table('cup_events', function (Blueprint $table) {
            $table->float('points')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->builder->table('cup_events', function (Blueprint $table) {
            $table->integer('points')->nullable(false)->change();
        });
    }
}
