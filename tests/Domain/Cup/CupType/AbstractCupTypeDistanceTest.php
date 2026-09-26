<?php

declare(strict_types=1);

namespace Tests\Domain\Cup\CupType;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupType\AbstractCupType;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\Criteria;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AbstractCupTypeDistanceTest extends TestCase
{
    #[Test]
    public function it_selects_cup_distances_by_event_and_group_ids(): void
    {
        $event = $this->createStub(CupEvent::class);
        $event->method('__get')->willReturnMap([['event_id', 9]]);
        $repository = $this->createMock(DistanceRepository::class);
        $repository->expects($this->once())->method('byCriteria')
            ->willReturnCallback(function (Criteria $criteria): Collection {
                $this->assertSame(9, $criteria->param('eventId'));
                $this->assertSame([12, 14], $criteria->param('groupIds'));

                return Collection::empty();
            });

        $this->assertCount(0, $this->type($repository)->distancesForGroups($event, collect([12, 14])));
    }

    #[Test]
    public function it_finds_a_distance_by_group_names(): void
    {
        $repository = $this->createMock(DistanceRepository::class);
        $repository->expects($this->once())->method('oneByCriteria')
            ->willReturnCallback(function (Criteria $criteria): ?Distance {
                $this->assertSame(9, $criteria->param('eventId'));
                $this->assertSame(['М21', 'М35'], $criteria->param('groupNames'));

                return null;
            });

        $this->assertNull($this->type($repository)->distanceForNames(['М21', 'М35'], 9));
    }

    #[Test]
    public function it_looks_up_equal_distances_with_matching_length_and_points(): void
    {
        $distance = $this->createStub(Distance::class);
        $distance->method('__get')->willReturnMap([
            ['event_id', 9], ['id', 4], ['length', 3500], ['points', 100],
        ]);
        $repository = $this->createMock(DistanceRepository::class);
        $repository->expects($this->once())->method('byCriteria')
            ->willReturnCallback(function (Criteria $criteria): Collection {
                $this->assertSame(9, $criteria->param('eventId'));
                $this->assertSame(4, $criteria->param('excludeId'));
                $this->assertSame(3500, $criteria->param('length'));
                $this->assertSame(100, $criteria->param('points'));

                return Collection::empty();
            });

        $this->assertCount(0, $this->type($repository)->matchingDistances($distance));
    }

    private function type(DistanceRepository $repository): TestCupType
    {
        return new TestCupType(
            $repository,
            new ProtocolLinesRepository($this->createStub(ConnectionInterface::class)),
            $this->createStub(GroupRepository::class),
            new CupGroupFactory(),
        );
    }
}

final class TestCupType extends AbstractCupType
{
    public function getNameKey(): string
    {
        return 'test';
    }

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): array
    {
        return [];
    }

    public function groups(): array
    {
        return [];
    }

    public function distancesForGroups(CupEvent $event, Collection $groups): Collection
    {
        return $this->cupEventDistancesByGroups($event, $groups);
    }

    public function distanceForNames(array $names, int $eventId): ?Distance
    {
        return $this->distanceByGroupNames($names, $eventId);
    }

    public function matchingDistances(Distance $distance): Collection
    {
        return $this->equalDistances($distance);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        return Collection::empty();
    }
}
