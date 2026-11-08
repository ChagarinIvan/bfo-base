<?php

declare(strict_types=1);

namespace Tests\Feature\Person;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ManualPersonRankMigrationTest extends TestCase
{
    #[Test]
    public function migration_roundtrip_preserves_protocol_rows_and_removes_manual_projection_on_rollback(): void
    {
        $manager = $this->app->make(DatabaseManager::class);
        $previous = $manager->getDefaultConnection();
        $this->app->make('config')->set('database.connections.manual_rank_migration', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $manager->setDefaultConnection('manual_rank_migration');
        try {
            $db = $manager->connection();
            $schema = $db->getSchemaBuilder();
            foreach (['person', 'protocol_lines', 'distances', 'events', 'competitions'] as $table) {
                $schema->create($table, static function (Blueprint $blueprint): void { $blueprint->id(); });
                $db->table($table)->insert(['id' => 1]);
            }
            $schema->create('person_rank_histories', static function (Blueprint $table): void {
                $table->id();
                $table->foreignId('person_id')->constrained('person');
                foreach (['protocol_line_id' => 'protocol_lines', 'distance_id' => 'distances', 'event_id' => 'events', 'competition_id' => 'competitions'] as $column => $foreign) {
                    $table->foreignId($column)->constrained($foreign);
                }
            });
            $db->table('person_rank_histories')->insert(['id' => 1, 'person_id' => 1, 'protocol_line_id' => 1, 'distance_id' => 1, 'event_id' => 1, 'competition_id' => 1]);
            $migration = require database_path('migrations/2026_11_08_000001_add_manual_person_ranks.php');
            $migration->up();
            $id = '00000000-0000-4000-8000-000000000001';
            $db->table('manual_person_ranks')->insert(['id' => $id, 'person_id' => 1, 'rank' => 6, 'achieved_on' => '2026-01-01', 'activated_on' => '2026-01-01', 'active' => true, 'created_at' => '2026-11-08', 'updated_at' => '2026-11-08', 'created_by' => 42, 'updated_by' => 42]);
            $db->table('person_rank_histories')->insert(['person_id' => 1, 'manual_rank_id' => $id]);
            $this->assertSame(2, $db->table('person_rank_histories')->count());
            $migration->down();
            $this->assertFalse($schema->hasTable('manual_person_ranks'));
            $this->assertSame(1, $db->table('person_rank_histories')->count());
            $this->assertSame(1, $db->table('person_rank_histories')->value('protocol_line_id'));
            $column = collect($schema->getColumns('person_rank_histories'))->firstWhere('name', 'protocol_line_id');
            $this->assertFalse($column['nullable']);
            $migration->up();
            $this->assertTrue($schema->hasColumn('person_rank_histories', 'manual_rank_id'));
        } finally {
            $manager->setDefaultConnection($previous);
            $manager->purge('manual_rank_migration');
        }
    }
}
