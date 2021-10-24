<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CupEvent;
use App\Models\Distance;
use App\Models\Event;
use App\Repositories\DistanceRepository;
use Illuminate\Support\Collection;

class DistanceService
{
    private DistanceRepository $distanceRepository;

    public function __construct(DistanceRepository $distanceRepository)
    {
        $this->distanceRepository = $distanceRepository;
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, Collection $groupNames): Collection
    {
        return $this->distanceRepository->getCupEventDistancesByGroups($cupEvent, $groups, $groupNames);
    }

    public function findDistance(int $groupId, int $eventId): ?Distance
    {
        return $this->distanceRepository->findDistance($groupId, $eventId);
    }

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
    }

    /**
     * @param Distance $mainDistance
     * @return Collection|Distance[]
     */
    public function getEqualDistances(Distance $mainDistance): Collection
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
