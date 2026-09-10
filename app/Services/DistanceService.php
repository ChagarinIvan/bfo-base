<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\Event;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

final readonly class DistanceService
{
    public function __construct(private DistanceRepository $distances)
    {
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, bool $withEquals = false): Collection
    {
        $distances = $this->distances->byCriteria(new Criteria([
            'eventId' => $cupEvent->event_id,
            'groupIds' => $groups->pluck('id')->all(),
        ]));

        if (!$withEquals) {
            return $distances;
        }

        $result = Collection::empty();

        foreach ($distances as $distance) {
            $result->add($distance);
            $result->push(...$this->getEqualDistances($distance)->values());
        }

        return $result->unique();
    }

    /**
     * @param string[] $groupNames
     */
    public function findDistance(array $groupNames, int $eventId): ?Distance
    {
        return $this->distances->oneByCriteria(new Criteria([
            'eventId' => $eventId,
            'groupNames' => $groupNames,
        ]));
    }

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
    }

    public function getEventGroupDistance(Event $event, int $groupId): ?Distance
    {
        return $this->distances->oneByCriteria(new Criteria([
            'eventId' => $event->id,
            'groupId' => $groupId,
        ]));
    }

    /** @return Collection<int, Distance> */
    public function getEqualDistances(Distance $mainDistance): Collection
    {
        return $this->distances->byCriteria(new Criteria([
            'eventId' => $mainDistance->event_id,
            'excludeId' => $mainDistance->id,
            'length' => $mainDistance->length,
            'points' => $mainDistance->points,
        ]));
    }

    public function byId(int $id): ?Distance
    {
        return $this->distances->oneByCriteria(new Criteria(['id' => $id]));
    }
}
