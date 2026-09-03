<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ranks');
    }

    public function down(): void
    {
        Schema::create('ranks', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('event_id');
            $table->string('rank');
            $table->date('start_date');
            $table->date('finish_date');
            $table->foreign('person_id', 'fk_rank_person_id')
                ->references('id')
                ->on('person')
                ->onDelete('cascade');
            $table->foreign('event_id', 'fk_rank_event_id')
                ->references('id')
                ->on('events')
                ->onDelete('cascade');
        });
    }
};
