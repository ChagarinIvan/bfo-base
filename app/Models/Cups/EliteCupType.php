<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use Illuminate\Support\Collection;

class EliteCupType extends AbstractCupType
{
    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.elite';
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $mainDistance = $this->distanceService->findDistance($group->id, $cupEvent->event_id);
        if ($mainDistance === null) {
            return new Collection();
        }
        $equalDistances = $this->distanceService->getEqualDistances($mainDistance);
        $distances = $equalDistances->add($mainDistance);
        return $this->protocolLinesRepository->getCupEventDistancesProtocolLines($distances, $cupEvent);
    }

    public function getGroups(): Collection
    {
        return $this->groupsService->getGroups([Group::M21E, Group::W21E]);
    }
}
