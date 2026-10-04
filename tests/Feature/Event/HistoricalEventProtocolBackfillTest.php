<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HistoricalEventProtocolBackfillTest extends TestCase
{
    #[Test]
    public function it_repairs_a_database_that_already_applied_the_earlier_backfill(): void
    {
        $previous = DB::getDefaultConnection();
        config()->set('database.connections.protocol_migration_test', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        DB::setDefaultConnection('protocol_migration_test');

        try {
            Schema::create('events', static function (Blueprint $table): void {
                $table->id();
                $table->string('file')->nullable();
            });
            Schema::create('distances', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('event_id');
            });
            Schema::create('protocol_lines', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('distance_id');
                $table->unsignedBigInteger('person_id')->nullable();
            });
            DB::table('events')->insert([
                ['id' => 1, 'file' => 'ready.html'],
                ['id' => 2, 'file' => 'partial.html'],
                ['id' => 3, 'file' => null],
            ]);
            DB::table('distances')->insert([
                ['id' => 11, 'event_id' => 1],
                ['id' => 12, 'event_id' => 2],
            ]);
            DB::table('protocol_lines')->insert([
                ['distance_id' => 11, 'person_id' => 101],
                ['distance_id' => 12, 'person_id' => null],
            ]);

            (require database_path('migrations/2026_09_13_000001_create_event_protocols_table.php'))->up();
            DB::table('events')->where('id', 1)->update(['processing_status' => 'ready']);
            (require database_path('migrations/2026_09_13_000003_require_event_processing_state.php'))->up();

            $events = DB::table('events')->orderBy('id')->get();
            $this->assertSame(['ready', 'identifyingError', 'parsingError'], $events->pluck('processing_status')->all());
            $this->assertCount(3, $events->pluck('processing_token')->unique());
            $this->assertNotNull($events[0]->processing_token);
        } finally {
            DB::setDefaultConnection($previous);
            DB::disconnect('protocol_migration_test');
        }
    }

    #[Test]
    public function it_classifies_every_historical_event_and_requires_a_token_and_status(): void
    {
        $previous = DB::getDefaultConnection();
        config()->set('database.connections.protocol_migration_test', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        DB::setDefaultConnection('protocol_migration_test');

        try {
            Schema::create('events', static function (Blueprint $table): void {
                $table->id();
                $table->string('file')->nullable();
            });
            Schema::create('distances', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('event_id');
            });
            Schema::create('protocol_lines', static function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('distance_id');
                $table->unsignedBigInteger('person_id')->nullable();
            });

            DB::table('events')->insert([
                ['id' => 1, 'file' => 'complete.html'],
                ['id' => 2, 'file' => 'partial.html'],
                ['id' => 3, 'file' => 'empty.html'],
                ['id' => 4, 'file' => null],
                ['id' => 5, 'file' => null],
            ]);
            DB::table('distances')->insert([
                ['id' => 11, 'event_id' => 1],
                ['id' => 12, 'event_id' => 2],
                ['id' => 13, 'event_id' => 5],
            ]);
            DB::table('protocol_lines')->insert([
                ['distance_id' => 11, 'person_id' => 101],
                ['distance_id' => 12, 'person_id' => null],
                ['distance_id' => 13, 'person_id' => 105],
            ]);

            (require database_path('migrations/2026_09_13_000001_create_event_protocols_table.php'))->up();
            (require database_path('migrations/2026_09_13_000002_backfill_historical_event_protocols.php'))->up();
            (require database_path('migrations/2026_09_13_000003_require_event_processing_state.php'))->up();

            $events = DB::table('events')->orderBy('id')->get();
            $this->assertSame(['ready', 'identifyingError', 'parsingError', 'parsingError', 'ready'], $events->pluck('processing_status')->all());
            $this->assertCount(5, $events->pluck('processing_token')->unique());
            foreach ($events as $event) {
                $this->assertNotEmpty($event->processing_token);
            }
            $this->assertNull($events[0]->error_message);
            $this->assertNotNull($events[1]->error_message);

            $this->expectException(QueryException::class);
            DB::table('events')->insert(['id' => 6, 'file' => null, 'processing_status' => null, 'processing_token' => null]);
        } finally {
            DB::setDefaultConnection($previous);
            DB::disconnect('protocol_migration_test');
        }
    }
}
