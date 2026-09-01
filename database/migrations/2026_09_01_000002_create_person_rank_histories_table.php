<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_rank_histories', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('person_id')->constrained('person')->cascadeOnDelete();
            $table->foreignId('protocol_line_id')->constrained('protocol_lines')->cascadeOnDelete();
            $table->foreignId('distance_id')->constrained('distances')->restrictOnDelete();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('competition_id')->constrained('competitions')->restrictOnDelete();
            $table->string('rank');
            $table->string('change_type');
            $table->date('achieved_on');
            $table->date('activated_on')->nullable();
            $table->date('started_on');
            $table->date('finished_on')->nullable();
            $table->index(['person_id', 'achieved_on', 'id']);
            $table->index('protocol_line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_rank_histories');
    }
};
