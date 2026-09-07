<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('event_flags');
        Schema::dropIfExists('flags');
    }

    public function down(): void
    {
        Schema::create('flags', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('color');
        });

        Schema::create('event_flags', static function (Blueprint $table): void {
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('flag_id');
            $table->index('event_id');
            $table->index('flag_id');
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('flag_id')->references('id')->on('flags')->cascadeOnDelete();
        });
    }
};
