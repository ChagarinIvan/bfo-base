<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        app(DatabaseManager::class)->connection()->getSchemaBuilder()->table('person', static function (Blueprint $table): void {
            $table->dateTime('rank_calculated_at')->nullable();
            $table->string('rank_calculation_reason', 32)->default('unknown');
        });

        app(DatabaseManager::class)->connection()->getSchemaBuilder()->table('person', static function (Blueprint $table): void {
            $table->string('rank_calculation_reason', 32)->default(null)->change();
        });
    }

    public function down(): void
    {
        app(DatabaseManager::class)->connection()->getSchemaBuilder()->table('person', static function (Blueprint $table): void {
            $table->dropColumn(['rank_calculated_at', 'rank_calculation_reason']);
        });
    }
};
