<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Event\Event;
use App\Models\CupEvent;
use App\Models\Distance;
use App\Repositories\DistanceRepository;
use Illuminate\Support\Collection;

class DistanceService
{
    public function __construct(private readonly DistanceRepository $distanceRepository)
    {
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, bool $withEquals = false): Collection
    {
        return $this->distanceRepository->getCupEventDistancesByGroups($cupEvent, $groups, $withEquals);
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

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
    }

    public function getEventGroupDistance(Event $event, int $groupId): ?Distance
    {
        return $this->distanceRepository->getEventGroupDistance($event->id, $groupId);
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
