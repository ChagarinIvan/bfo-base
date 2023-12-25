<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullToEventIdInRanksTable extends Migration
{
    public function up(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->unsignedBigInteger('event_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
        });
    }
}
