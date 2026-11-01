<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\ProtocolParser;
use App\Domain\Group\Group;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Testing\Fakes\EventFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_fill;
use function array_filter;
use function str_contains;

final class StandardProtocolLinesFactoryTest extends TestCase
{
    use RefreshDatabase;

    public static function parsedLineCounts(): iterable
    {
        yield 'single line' => [1];
        yield 'multiple lines' => [2];
    }

    public static function distinctGroupCounts(): iterable
    {
        yield 'one group' => [1];
        yield 'fifty groups' => [50];
    }

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
    #[DataProvider('parsedLineCounts')]
    public function parsing_passes_the_operation_actor_to_the_line_factory(int $lineCount): void
    {
        $this->app->instance('events', new EventFake($this->app->make('events')));
        $competition = Competition::factory()->createOne();
        $event = Event::factory()->createOne([
            'competition_id' => $competition->id,
            'processing_status' => EventProcessingStatus::PARSING,
            'created_by' => 7,
        ]);
        $parser = $this->createMock(ProtocolParser::class);
        $parser->expects($this->once())->method('parse')->willReturn(array_fill(0, $lineCount, $this->input()));
        $this->app->instance(ProtocolParser::class, $parser);
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse('2026-10-27 12:34:56')));

        $this->app->make(ParseEventProtocolService::class)->execute(
            new ParseEventProtocol($event->id, $event->processing_token, 42),
        );

        $lines = $event->protocolLines()->get();
        $this->assertCount($lineCount, $lines);
        foreach ($lines as $line) {
            $this->assertInstanceOf(ProtocolLine::class, $line);
            $this->assertSame(42, $line->created->by);
            $this->assertSame('2026-10-27 12:34:56', $line->created->at->toDateTimeString());
            $this->assertEquals($line->created, $line->updated);
        }
        $this->assertSame(EventProcessingStatus::IDENTIFYING, $event->fresh()->processing_status);
    }

    #[Test]
    #[DataProvider('distinctGroupCounts')]
    public function existing_groups_are_loaded_in_one_query(int $count): void
    {
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $inputs = [];
        for ($i = 1; $i <= $count; ++$i) {
            Group::factory()->createOne(['id' => 1000 + $i, 'name' => 'Group ' . $i]);
            $inputs[] = $this->input('Group ' . $i, $this->app->make(GroupNameNormalizer::class)->normalize('Group ' . $i));
        }
        $connection = $this->app->make(Connection::class);
        $connection->enableQueryLog();
        $connection->flushQueryLog();
        try {
            $lines = $this->app->make(StandardProtocolLinesFactory::class)->create($event, $inputs, 42);
            $queries = array_filter($connection->getQueryLog(), static fn (array $query): bool => str_contains($query['query'], 'from `groups`'));
            $this->assertCount(1, $queries);
            $this->assertCount($count, $lines);
            foreach ($lines as $i => $line) {
                $distance = Distance::query()->findOrFail($line->distance_id);
                $this->assertSame($inputs[$i]->normalizedGroupName, $distance->group->normalize_name);
            }
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }
    }

    #[Test]
    public function a_repeated_unknown_group_is_created_once_and_an_inactive_one_is_not_reused(): void
    {
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $inactive = Group::factory()->createOne(['name' => 'M21', 'normalize_name' => 'm21', 'active' => false]);
        $lines = $this->app->make(StandardProtocolLinesFactory::class)->create($event, [$this->input(), $this->input()], 42);

        $this->assertCount(2, $lines);
        $this->assertSame($lines[0]->distance_id, $lines[1]->distance_id);
        $groupId = Distance::query()->findOrFail($lines[0]->distance_id)->group_id;
        $this->assertNotSame($inactive->id, $groupId);
        $this->assertSame(1, Group::query()->where('normalize_name', 'm21')->where('active', true)->count());
    }

    #[Test]
    public function caches_do_not_reuse_a_previous_events_distance_or_a_disabled_group(): void
    {
        $competition = Competition::factory()->createOne();
        $first = Event::factory()->createOne(['competition_id' => $competition->id]);
        $second = Event::factory()->createOne(['competition_id' => $competition->id]);
        $factory = $this->app->make(StandardProtocolLinesFactory::class);
        $firstLine = $factory->create($first, [$this->input()], 42)[0];
        $firstDistance = Distance::query()->findOrFail($firstLine->distance_id);
        Group::query()->whereKey($firstDistance->group_id)->update(['active' => false]);

        $secondLine = $factory->create($second, [$this->input()], 42)[0];
        $secondDistance = Distance::query()->findOrFail($secondLine->distance_id);
        $this->assertSame($second->id, $secondDistance->event_id);
        $this->assertNotSame($firstDistance->group_id, $secondDistance->group_id);
    }

    #[Test]
    public function the_same_active_group_gets_a_separate_distance_for_each_event(): void
    {
        $competition = Competition::factory()->createOne();
        $first = Event::factory()->createOne(['competition_id' => $competition->id]);
        $second = Event::factory()->createOne(['competition_id' => $competition->id]);
        $group = Group::factory()->createOne(['name' => 'M21', 'normalize_name' => 'm21']);
        $factory = $this->app->make(StandardProtocolLinesFactory::class);
        $firstLine = $factory->create($first, [$this->input()], 42)[0];
        $secondLine = $factory->create($second, [$this->input()], 42)[0];

        $firstDistance = Distance::query()->findOrFail($firstLine->distance_id);
        $secondDistance = Distance::query()->findOrFail($secondLine->distance_id);
        $this->assertSame($group->id, $firstDistance->group_id);
        $this->assertSame($group->id, $secondDistance->group_id);
        $this->assertSame($first->id, $firstDistance->event_id);
        $this->assertSame($second->id, $secondDistance->event_id);
        $this->assertNotSame($firstLine->distance_id, $secondLine->distance_id);
    }

    #[Test]
    public function empty_input_does_not_query_groups(): void
    {
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $connection = $this->app->make(Connection::class);
        $connection->enableQueryLog();
        $connection->flushQueryLog();
        try {
            $this->app->make(StandardProtocolLinesFactory::class)->create($event, [], 42);
            $queries = array_filter($connection->getQueryLog(), static fn (array $query): bool => str_contains($query['query'], 'from `groups`'));
            $this->assertCount(0, $queries);
        } finally {
            $connection->disableQueryLog();
            $connection->flushQueryLog();
        }
    }

    private function input(string $group = 'M21', string $normalizedName = 'm21'): ProtocolLineInput
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
            group: $group,
            normalizedGroupName: $normalizedName,
            distanceLength: 1000,
            distancePoints: 10,
        );
    }
}
