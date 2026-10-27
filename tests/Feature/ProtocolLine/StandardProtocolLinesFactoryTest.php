<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\ProtocolParser;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Testing\Fakes\EventFake;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardProtocolLinesFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_a_missing_athlete_rank_from_a_protocol(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        $lines = app(StandardProtocolLinesFactory::class)->create($event, [new ProtocolLineInput(
            serialNumber: 1,
            lastname: 'Runner',
            firstname: 'Test',
            club: 'Club',
            year: 2000,
            rank: null,
            runnerNumber: 1,
            time: null,
            place: null,
            completeRank: null,
            points: null,
            vk: false,
            group: 'M21',
            normalizedGroupName: 'm21',
            distanceLength: 1000,
            distancePoints: 10,
        )], 42);

        $this->assertSame('', $lines[0]->rank);
    }

    #[Test]
    public function it_stamps_new_lines_with_the_factory_clock_and_current_actor_and_persists_them(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'created_at' => '2020-01-01 00:00:00',
            'created_by' => 7,
        ]);
        $now = Carbon::parse('2026-10-27 12:34:56');
        $this->app->instance(Clock::class, new FrozenClock($now));
        $factory = $this->app->make(StandardProtocolLinesFactory::class);
        $lines = $factory->create($event, [$this->input(), $this->input()], 42);

        foreach ($lines as $line) {
            $this->assertSame('2026-10-27 12:34:56', $line->created->at->toDateTimeString());
            $this->assertSame(42, $line->created->by);
            $this->assertEquals($line->created, $line->updated);
        }
        $this->app->make(ProtocolLineRepository::class)->add(...$lines);
        foreach ($lines as $line) {
            $this->assertEquals($line->created, $line->fresh()->created);
            $this->assertEquals($line->updated, $line->fresh()->updated);
        }

        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-10-28 13:00:00')));
        $reimported = $this->app->make(StandardProtocolLinesFactory::class)->create($event, [$this->input()], 43);
        $this->assertSame('2026-10-28 13:00:00', $reimported[0]->created->at->toDateTimeString());
        $this->assertSame(43, $reimported[0]->created->by);
        $this->assertEquals($reimported[0]->created, $reimported[0]->updated);
    }

    #[Test]
    public function empty_input_does_not_create_lines_or_read_the_clock(): void
    {
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        $clock = $this->createMock(Clock::class);
        $clock->expects($this->never())->method('now');
        $this->app->instance(Clock::class, $clock);

        $this->assertSame([], $this->app->make(StandardProtocolLinesFactory::class)->create($event, [], 42));
    }

    #[Test]
    public function parsing_passes_the_operation_actor_to_the_line_factory(): void
    {
        $this->app->instance('events', new EventFake($this->app->make('events')));
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::PARSING,
            'created_by' => 7,
        ]);
        $parser = $this->createMock(ProtocolParser::class);
        $parser->expects($this->once())->method('parse')->willReturn([$this->input()]);
        $this->app->instance(ProtocolParser::class, $parser);
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-10-27 12:34:56')));

        $this->app->make(ParseEventProtocolService::class)->execute(
            new ParseEventProtocol($event->id, $event->processing_token, 42),
        );

        $line = $event->protocolLines()->firstOrFail();
        $this->assertInstanceOf(ProtocolLine::class, $line);
        $this->assertSame(42, $line->created->by);
        $this->assertSame('2026-10-27 12:34:56', $line->created->at->toDateTimeString());
        $this->assertEquals($line->created, $line->updated);
        $this->assertSame(EventProcessingStatus::IDENTIFYING, $event->fresh()->processing_status);
    }

    private function input(): ProtocolLineInput
    {
        return new ProtocolLineInput(
            serialNumber: 1,
            lastname: 'Runner',
            firstname: 'Test',
            club: 'Club',
            year: 2000,
            rank: null,
            runnerNumber: 1,
            time: null,
            place: null,
            completeRank: null,
            points: null,
            vk: false,
            group: 'M21',
            normalizedGroupName: 'm21',
            distanceLength: 1000,
            distancePoints: 10,
        );
    }
}
