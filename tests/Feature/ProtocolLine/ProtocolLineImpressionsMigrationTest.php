<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use stdClass;
use Tests\TestCase;
use function in_array;

final class ProtocolLineImpressionsMigrationTest extends TestCase
{
    private Connection $connection;
    private string $previousConnection;

    public static function invalidSources(): iterable
    {
        yield 'null distance' => ['nullDistance'];
        yield 'missing distance' => ['distance'];
        yield 'missing event' => ['event'];
        foreach (['created_at', 'created_by', 'updated_at', 'updated_by'] as $column) {
            yield $column => [$column];
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $manager = $this->app->make(DatabaseManager::class);
        $this->previousConnection = $manager->getDefaultConnection();
        $this->app->make('config')->set('database.connections.line_impressions_test', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
        $manager->setDefaultConnection('line_impressions_test');
        $this->connection = $manager->connection();
        $schema = $this->connection->getSchemaBuilder();
        $schema->create('events', static function (Blueprint $table): void {
            $table->id();
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
            $table->boolean('active');
            $table->string('processing_status');
        });
        $schema->create('distances', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
        });
        $schema->create('protocol_lines', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('distance_id')->nullable();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('lastname');
        });
    }

    protected function tearDown(): void
    {
        $manager = $this->app->make(DatabaseManager::class);
        $manager->setDefaultConnection($this->previousConnection);
        $manager->purge('line_impressions_test');
        parent::tearDown();
    }

    #[Test]
    public function it_copies_both_event_impressions_without_changing_results_or_dispatching_events(): void
    {
        $this->seedHistory();
        $before = $this->connection->table('protocol_lines')->orderBy('id')->get()->all();
        $dispatched = [];
        $this->app->make('events')->listen('App\\Domain\\*', static function ($name) use (&$dispatched): void {
            $dispatched[] = $name;
        });

        (require database_path('migrations/2026_10_27_000001_add_protocol_line_impressions.php'))->up();

        $rows = $this->connection->table('protocol_lines')->orderBy('id')->get();
        $this->assertCount(2, $rows);
        foreach ($rows as $index => $row) {
            $event = $this->connection->table('events')->find($index + 1);
            $this->assertInstanceOf(stdClass::class, $event);
            foreach (['created_at', 'created_by', 'updated_at', 'updated_by'] as $column) {
                $this->assertSame($event->$column, $row->$column);
                unset($row->$column);
            }
            $this->assertEquals($before[$index], $row);
        }
        $this->assertSame([], $dispatched);
        $columns = $this->connection->getSchemaBuilder()->getColumns('protocol_lines');
        foreach ($columns as $column) {
            if (in_array($column['name'], ['created_at', 'created_by', 'updated_at', 'updated_by'], true)) {
                $this->assertFalse($column['nullable']);
            }
        }
    }

    #[Test]
    public function it_preserves_results_on_rollback_and_reloads_current_event_audit_on_reapplication(): void
    {
        $this->seedHistory();
        $before = $this->connection->table('protocol_lines')->orderBy('id')->get()->all();
        $migration = (require database_path('migrations/2026_10_27_000001_add_protocol_line_impressions.php'));
        $migration->up();
        $migration->down();
        $this->assertEquals($before, $this->connection->table('protocol_lines')->orderBy('id')->get()->all());
        $this->connection->table('events')->where('id', 1)->update(['updated_by' => 99]);
        $migration->up();
        $line = $this->connection->table('protocol_lines')->find(11);
        $this->assertInstanceOf(stdClass::class, $line);
        $this->assertSame(99, $line->updated_by);
    }

    #[Test]
    public function it_supports_an_empty_database(): void
    {
        $migration = (require database_path('migrations/2026_10_27_000001_add_protocol_line_impressions.php'));
        $migration->up();
        $this->assertTrue($this->connection->getSchemaBuilder()->hasColumn('protocol_lines', 'created_at'));
        $migration->down();
        $this->assertFalse($this->connection->getSchemaBuilder()->hasColumn('protocol_lines', 'created_at'));
        $this->assertSame(0, $this->connection->table('protocol_lines')->count());
    }

    #[Test]
    #[DataProvider('invalidSources')]
    public function it_rejects_an_incomplete_source_before_changing_the_schema(string $source): void
    {
        $this->seedHistory();
        match ($source) {
            'nullDistance' => $this->connection->table('protocol_lines')->where('id', 11)->update(['distance_id' => null]),
            'distance' => $this->connection->table('distances')->where('id', 101)->delete(),
            'event' => $this->connection->table('events')->where('id', 1)->delete(),
            default => $this->connection->table('events')->where('id', 1)->update([$source => null]),
        };
        try {
            (require database_path('migrations/2026_10_27_000001_add_protocol_line_impressions.php'))->up();
            $this->fail('Expected an invalid source to abort the migration.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('11', $exception->getMessage());
            $this->assertFalse($this->connection->getSchemaBuilder()->hasColumn('protocol_lines', 'created_at'));
            $this->assertSame(2, $this->connection->table('protocol_lines')->count());
        }
    }

    private function seedHistory(): void
    {
        $this->connection->table('events')->insert([
            ['id' => 1, 'created_at' => '2020-01-01 10:00:00', 'created_by' => 3, 'updated_at' => '2021-02-01 12:00:00', 'updated_by' => 4, 'active' => true, 'processing_status' => 'ready'],
            ['id' => 2, 'created_at' => '2022-03-01 08:00:00', 'created_by' => 5, 'updated_at' => '2023-04-01 16:00:00', 'updated_by' => 6, 'active' => false, 'processing_status' => 'parsingError'],
        ]);
        $this->connection->table('distances')->insert([['id' => 101, 'event_id' => 1], ['id' => 102, 'event_id' => 2]]);
        $this->connection->table('protocol_lines')->insert([
            ['id' => 11, 'distance_id' => 101, 'person_id' => 7, 'lastname' => 'Runner'],
            ['id' => 12, 'distance_id' => 102, 'person_id' => null, 'lastname' => 'Other'],
        ]);
    }
}
