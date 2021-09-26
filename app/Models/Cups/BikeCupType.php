<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group;
use Illuminate\Support\Collection;

class BikeCupType extends EliteCupType
{
    private const M_GROUP = 'M';
    private const W_GROUP = 'Ж';

    public function getId(): string
    {
        return CupType::BIKE;
    }

    public function getName(): string
    {
        return 'Вело';
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

    /**
     * @param Collection|Group[] $groups
     * @return Collection
     */
    public function getCupGroups(Collection|array $groups): Collection
    {
        $resultGroups = new Collection();
        $hasMale = false;
        $hasFeMale = false;
        foreach ($groups as $group) {
            if ($hasMale === false && $group->isMale()) {
                $resultGroups->push($group);
                $group->name = self::M_GROUP;
                $hasMale = true;
            }
            if ($hasFeMale === false && $group->isFeMale()) {
                $resultGroups->push($group);
                $group->name = self::W_GROUP;
                $hasFeMale = true;
            }
        }
        return $resultGroups;
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
        $eventGroups = $this->groupsRepository->getEventGroups($cupEvent->event_id)->keyBy('name');
        $maleGroups = $mainGroup->maleGroups()->flip();
        $cupGroups = $cupEvent->cup->groups()->get()->keyBy('name');
        $cupGroups = $cupGroups->intersectByKeys($maleGroups);
        $cupGroups = $cupGroups->intersectByKeys($eventGroups);

        if ($cupGroups->count() > 1) {
            throw new \RuntimeException('Many groups');
        }

        return $cupGroups->first();
    }
}
