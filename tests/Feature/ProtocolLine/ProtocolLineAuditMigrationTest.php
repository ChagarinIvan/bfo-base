<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLine;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function in_array;

final class ProtocolLineAuditMigrationTest extends TestCase
{
    use DatabaseTruncation;

    #[Test]
    public function existing_lines_inherit_both_impressions_from_their_own_event(): void
    {
        // DDL commits on MySQL; let the following test rebuild its database normally.
        $this->beforeApplicationDestroyed(static function (): void {
            RefreshDatabaseState::$migrated = false;
        });
        $this->seed(ProtocolLinesSeeder::class);
        $expected = [];
        foreach ([101, 102] as $id) {
            $event = Event::query()->findOrFail($id);
            $event->created = new Impression(Carbon::parse('2022-01-01 10:00:00'), $id);
            $event->updated = new Impression(Carbon::parse('2022-03-01 11:00:00'), $id + 10);
            $event->save();
            $expected[$id] = [$event->created, $event->updated];
        }
        $migration = require $this->app->databasePath('migrations/2026_10_24_000001_add_rank_audit.php');
        $migration->down();
        $migration->up();

        foreach (ProtocolLine::query()->with('distance')->get() as $line) {
            [$created, $updated] = $expected[$line->distance->event_id];
            $this->assertEquals($created, $line->created);
            $this->assertEquals($updated, $line->updated);
        }
        $columns = ProtocolLine::query()->getModel()->getConnection()->getSchemaBuilder()->getColumns('protocol_lines');
        foreach ($columns as $column) {
            if (in_array($column['name'], ['created_at', 'created_by', 'updated_at', 'updated_by'], true)) {
                $this->assertFalse($column['nullable']);
            }
        }
    }
}
