<?php

declare(strict_types=1);

namespace Tests\Feature\Person;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonRankCalculationMigrationTest extends TestCase
{
    #[Test]
    public function up_and_down_preserve_existing_ranks_without_inventing_calculation_dates(): void
    {
        $manager = $this->app->make(DatabaseManager::class);
        $previous = $manager->getDefaultConnection();
        $this->app->make('config')->set('database.connections.rank_metadata_test', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $manager->setDefaultConnection('rank_metadata_test');
        try {
            $connection = $manager->connection();
            $schema = $connection->getSchemaBuilder();
            $schema->create('person', static function (Blueprint $table): void {
                $table->id();
                $table->integer('current_rank');
            });
            $connection->table('person')->insert(['id' => 1, 'current_rank' => 6]);
            $migration = require database_path('migrations/2026_11_01_000001_add_person_rank_calculation_metadata.php');
            $migration->up();
            $row = $connection->table('person')->first();
            $this->assertNotNull($row);
            $this->assertSame(6, $row->current_rank);
            $this->assertNull($row->rank_calculated_at);
            $this->assertNull($row->rank_calculation_reason);
            $connection->table('person')->insert(['id' => 2, 'current_rank' => 0]);
            $this->assertNull($connection->table('person')->where('id', 2)->value('rank_calculated_at'));
            $migration->down();
            $this->assertFalse($schema->hasColumn('person', 'rank_calculated_at'));
            $this->assertFalse($schema->hasColumn('person', 'rank_calculation_reason'));
            $this->assertSame(6, $connection->table('person')->where('id', 1)->value('current_rank'));
            $migration->up();
            $this->assertNull($connection->table('person')->where('id', 1)->value('rank_calculation_reason'));
        } finally {
            $manager->setDefaultConnection($previous);
            $manager->purge('rank_metadata_test');
        }
    }
}
