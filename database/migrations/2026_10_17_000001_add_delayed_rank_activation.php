<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $schema = app(DatabaseManager::class)->connection()->getSchemaBuilder();
        $schema->table('protocol_lines', static function (Blueprint $table): void {
            $table->boolean('delayed_rank_activation')->default(false);
        });
        $schema->table('person', static function (Blueprint $table): void {
            $table->date('next_rank_activation_on')->nullable();
            $table->index('next_rank_activation_on', 'person_next_rank_activation_index');
        });
    }

    public function down(): void
    {
        $schema = app(DatabaseManager::class)->connection()->getSchemaBuilder();
        $schema->table('person', static function (Blueprint $table): void {
            $table->dropIndex('person_next_rank_activation_index');
            $table->dropColumn('next_rank_activation_on');
        });
        $schema->table('protocol_lines', static function (Blueprint $table): void {
            $table->dropColumn('delayed_rank_activation');
        });
    }
};
