<?php

namespace App\Services;

use App\Models\CupEvent;
use App\Models\Distance;
use App\Models\Event;
use App\Repositories\DistanceRepository;
use Illuminate\Support\Collection;

class DistanceService
{
    public function __construct(private DistanceRepository $distanceRepository)
    {}

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups): Collection
    {
        return $this->distanceRepository->getCupEventDistancesByGroups($cupEvent, $groups);
    }

    /**
     * @param string[] $groupNames
     * @param int $eventId
     * @return Distance|null
     */
    public function findDistance(array $groupNames, int $eventId): ?Distance
    {
        return $this->distanceRepository->findDistance($groupNames, $eventId);
    }

    /**
     * @param string[] $groupNames
     * @param int $eventId
     * @return Collection|Distance[]
     */
    public function findDistances(array $groupNames, int $eventId): array|Collection
    {
        return $this->distanceRepository->findDistances($groupNames, $eventId);
    }

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
    }

    /**
     * @param Distance $mainDistance
     * @return Collection|Distance[]
     */
    public function getEqualDistances(Distance $mainDistance): array|Collection
    {
        return $this->distanceRepository->getEqualDistances($mainDistance);
    }

    public function updateDistanceGroup(Distance $distance, int $groupId): Distance
    {
        $distance->group_id = $groupId;
        $distance->save();
        return $distance;
    }
}
