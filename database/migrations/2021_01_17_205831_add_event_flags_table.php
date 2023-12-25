<?php

declare(strict_types=1);

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
    public function up(): void
    {
        Schema::create('flags', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('color')->nullable(false);
        });

        Schema::create('event_flags', static function (Blueprint $table): void {
            $table->bigInteger('event_id')->unsigned()->nullable(false)->index();
            $table->bigInteger('flag_id')->unsigned()->nullable(false)->index();
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
    public function down(): void
    {
        Schema::dropIfExists('event_flag');
        Schema::dropIfExists('club');
    }
}
