<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use Illuminate\Support\Collection;

class BikeCupType extends EliteCupType
{
    public function getId(): string
    {
        return CupType::BIKE;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.bike';
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $this->getGetRealGroup($cupEvent, $mainGroup));
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $realGroup = $this->getGetRealGroup($cupEvent, $group);
        if ($realGroup === null) {
            return Collection::empty();
        }
        return parent::getGroupProtocolLines($cupEvent, $realGroup);
    }

    private function getGetRealGroup(CupEvent $cupEvent, Group $mainGroup): ?Group
    {
        $eventGroups = $this->groupsService->getCupEventGroups($cupEvent)->keyBy('name');
        $maleGroups = $mainGroup->maleGroups()->flip();
        $cupGroups = $this->groupsService->getGroups([Group::M21E, Group::W21E, Group::M21_MTBO, Group::W21_MTBO])
            ->keyBy('name');

        $cupGroups = $cupGroups->intersectByKeys($maleGroups);
        $cupGroups = $cupGroups->intersectByKeys($eventGroups);

        if ($cupGroups->count() > 1) {
            throw new \RuntimeException('Many groups');
        }

        return $cupGroups->first();
    }

    public function getGroups(): Collection
    {
        return $this->groupsService->getGroups([Group::M21E, Group::W21E]);
    }
}
