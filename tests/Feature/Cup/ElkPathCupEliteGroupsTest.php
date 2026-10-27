<?php

declare(strict_types=1);

namespace Tests\Feature\Cup;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupType;
use App\Domain\Cup\CupType\ElkPathCup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use App\Domain\Event\StandardProtocolParser;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Storage;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_column;
use function array_first;
use function array_keys;
use function array_unique;
use function count;
use function file_get_contents;

final class ElkPathCupEliteGroupsTest extends TestCase
{
    use RefreshDatabase;

    private int $nextPersonId = 1000;

    /** @return iterable<string, array{string, string}> */
    public static function existingGroups(): iterable
    {
        yield 'men old label' => ['Elite–Mужчыны', 'M_0_Elite–M'];
        yield 'men old trail' => ['EliteTrail-М', 'M_0_Elite–M'];
        yield 'men compact night' => ['NigthTrailElite-М', 'M_0_Elite–M'];
        yield 'men spaced night' => ['Night Trail Elite - М', 'M_0_Elite–M'];
        yield 'men another prefix' => ['Autumn Cup Elite  -  М', 'M_0_Elite–M'];
        yield 'men spaced exact alias' => ['Elite Trail - М', 'M_0_Elite–M'];
        yield 'women old label' => ['Elite–Жанчыны', 'W_0_Elite–W'];
        yield 'women old trail' => ['EliteTrail-Ж', 'W_0_Elite–W'];
        yield 'women compact night' => ['NigthTrailElite-Ж', 'W_0_Elite–W'];
        yield 'women spaced night' => ['Night Trail Elite - Ж', 'W_0_Elite–W'];
        yield 'women another prefix' => ['Autumn Cup Elite  -  Ж', 'W_0_Elite–W'];
        yield 'women spaced exact alias' => ['Elite Trail - Ж', 'W_0_Elite–W'];
        yield 'short men' => ['NightTrailShort-М,15-34', 'M_0_Short–M'];
        yield 'short men spaced' => ['Night Trail Short - М, 15-34', 'M_0_Short–M'];
        yield 'short women' => ['NightTrailShort-Ж,15-34', 'W_0_Short–W'];
        yield 'short masters men' => ['NightTrailShort-М,35+', 'M_35_Short–M-35'];
        yield 'short masters women' => ['NightTrailShort-Ж,35+', 'W_35_Short–W-35'];
        yield 'youth men' => ['NightTrailYouth-М', 'M_0_Youth–M'];
        yield 'youth women' => ['NightTrailYouth-Ж', 'W_0_Youth–W'];
        yield 'kids men' => ['NightTrailKids-М', 'M_0_Kids–M'];
        yield 'kids women' => ['NightTrailKids-Ж', 'W_0_Kids–W'];
    }

    #[Test]
    public function it_calculates_both_elite_groups_from_the_imported_night_protocol(): void
    {
        $stage = $this->stage();
        $lines = $this->importNightProtocol($stage->event);
        $this->assertCount(92, $lines);
        $this->assertSame(92, ProtocolLine::query()->count());
        $calculator = $this->app->make(ElkPathCup::class);
        $connection = $this->app->make(ConnectionInterface::class);
        $groups = [
            ['Nigth Trail Elite - М', 'M_0_Elite–M', 19],
            ['Nigth Trail Elite - Ж', 'W_0_Elite–W', 6],
        ];
        $allIds = [];

        foreach ($groups as [$name, $groupId, $expectedCount]) {
            $expectedIds = ProtocolLine::query()
                ->whereHas('distance.group', static fn ($query) => $query->where('name', $name))
                ->pluck('id')->all();
            $this->assertCount($expectedCount, $expectedIds);
            $connection->flushQueryLog();
            $connection->enableQueryLog();
            try {
                $points = $calculator->calculateEvent($stage, CupGroupFactory::fromId($groupId));
                $queryCount = count($connection->getQueryLog());
            } finally {
                $connection->disableQueryLog();
            }

            $this->assertEqualsCanonicalizing($expectedIds, array_column(array_column($points, 'protocolLine'), 'id'));
            $this->assertCount($expectedCount, $points);
            $this->assertSame(1000, array_first($points)->points);
            $this->assertLessThanOrEqual(6, $queryCount, 'Group selection must not query once per athlete.');
            $allIds = [...$allIds, ...array_keys($points)];
        }

        $this->assertCount(25, array_unique($allIds));
    }

    #[Test]
    public function clearing_a_stale_table_rebuilds_both_groups_from_the_saved_protocol(): void
    {
        $stage = $this->stage();
        $this->importNightProtocol($stage->event);
        $cache = $this->app->make(CacheManager::class);
        $builder = $this->app->make(CupTableBuilder::class);
        $events = new Collection([$stage]);
        $groups = ['M_0_Elite–M' => 19, 'W_0_Elite–W' => 6];
        foreach ($groups as $id => $count) {
            $cache->tags(['cups'])->put("table_{$stage->cup_id}_{$id}", new CupTable([], []), 60);
            $this->assertSame([], $builder->build($stage->cup, $events, CupGroupFactory::fromId($id))->rows);
        }

        $this->app->make(ClearCupCacheService::class)->execute();

        foreach ($groups as $id => $count) {
            $table = $builder->build($stage->cup, $events, CupGroupFactory::fromId($id));
            $this->assertCount($count, $table->rows);
        }
        $this->assertSame(92, ProtocolLine::query()->count());
    }

    #[Test]
    #[DataProvider('existingGroups')]
    public function it_preserves_existing_group_aliases_and_points(string $name, string $groupId): void
    {
        $stage = $this->stage();
        $group = Group::factory()->createOne(['id' => 1001, 'name' => $name]);
        $distance = Distance::factory()->createOne(['id' => 1001, 'event_id' => $stage->event_id, 'group_id' => $group->id]);
        $first = $this->line($distance, '00:20:00');
        $second = $this->line($distance, '00:25:00');
        $calculator = $this->app->make(ElkPathCup::class);

        $points = $calculator->calculateEvent($stage, CupGroupFactory::fromId($groupId));

        $this->assertCount(2, $points);
        $this->assertSame(1000, $points[$first->person_id]->points);
        $this->assertSame(600, $points[$second->person_id]->points);
        $otherId = $groupId === 'M_0_Elite–M' ? 'W_0_Elite–W' : 'M_0_Elite–M';
        $this->assertSame([], $calculator->calculateEvent($stage, CupGroupFactory::fromId($otherId)));
    }

    #[Test]
    public function it_does_not_treat_an_unknown_trail_group_as_elite(): void
    {
        $stage = $this->stage();
        $group = Group::factory()->createOne(['id' => 1001, 'name' => 'Nigth Trail Elite Open - М']);
        $distance = Distance::factory()->createOne(['id' => 1001, 'event_id' => $stage->event_id, 'group_id' => $group->id]);
        $this->line($distance, '00:20:00');
        $calculator = $this->app->make(ElkPathCup::class);

        $this->assertSame([], $calculator->calculateEvent($stage, CupGroupFactory::fromId('M_0_Elite–M')));
        $this->assertSame([], $calculator->calculateEvent($stage, CupGroupFactory::fromId('W_0_Elite–W')));
    }

    #[Test]
    public function ignoring_spaces_is_explicit_and_does_not_change_the_saved_name(): void
    {
        $stage = $this->stage();
        $name = '  Elite Trail - М  ';
        $group = Group::factory()->createOne(['id' => 1001, 'name' => $name]);
        $distance = Distance::factory()->createOne(['id' => 1001, 'event_id' => $stage->event_id, 'group_id' => $group->id]);
        $repository = $this->app->make(DistanceRepository::class);

        $this->assertNull($repository->oneByCriteria(new Criteria([
            'eventId' => $stage->event_id,
            'groupNames' => ['EliteTrail-М'],
        ])));
        $this->assertSame($distance->id, $repository->oneByCriteria(new Criteria([
            'eventId' => $stage->event_id,
            'groupNamesIgnoringSpaces' => [' Elite Trail-М '],
        ]))?->id);
        $this->assertSame($name, $group->fresh()->name);
        $this->assertNull($repository->oneByCriteria(new Criteria([
            'eventId' => $stage->event_id,
            'groupNamesIgnoringSpaces' => [],
        ])));
    }

    private function stage(): CupEvent
    {
        $competition = Competition::factory()->createOne(['id' => 1001]);
        $event = Event::factory()->createOne([
            'id' => 1001,
            'competition_id' => $competition->id,
            'active' => true,
            'processing_status' => EventProcessingStatus::READY,
            'date' => '2026-10-17',
            'file' => '2026/night@@openxmlformats',
        ]);
        $cup = Cup::factory()->createOne(['id' => 1001, 'type' => CupType::ELK_PATH, 'events_count' => 1, 'year' => '2026']);
        $this->assertInstanceOf(Cup::class, $cup);
        $stage = CupEvent::factory()->createOne(['id' => 1001, 'event_id' => $event->id, 'cup_id' => $cup->id, 'points' => 1000]);
        $this->assertInstanceOf(CupEvent::class, $stage);
        $stage->setRelation('event', $event);
        $stage->setRelation('cup', $cup);

        return $stage;
    }

    /** @return list<ProtocolLine> */
    private function importNightProtocol(Event $event): array
    {
        $storage = $this->createMock(Storage::class);
        $storage->expects($this->once())->method('get')->with($event->file)
            ->willReturn(file_get_contents(__DIR__ . '/../../Fixtures/Protocols/2026/Results_night_cup.xlsx'));
        $this->app->instance(Storage::class, $storage);
        $inputs = $this->app->make(StandardProtocolParser::class)->parse($event);
        $lines = $this->app->make(StandardProtocolLinesFactory::class)->create($event, $inputs, $event->created_by);
        foreach ($lines as $line) {
            $person = Person::factory()->createOne(['id' => ++$this->nextPersonId, 'active' => true]);
            $line->person_id = $person->id;
        }
        $this->app->make(ProtocolLineRepository::class)->add(...$lines);

        return $lines;
    }

    private function line(Distance $distance, string $time): ProtocolLine
    {
        $person = Person::factory()->createOne(['id' => ++$this->nextPersonId, 'active' => true]);

        return ProtocolLine::factory()->createOne(['distance_id' => $distance->id, 'person_id' => $person->id, 'time' => $time, 'vk' => false]);
    }
}
