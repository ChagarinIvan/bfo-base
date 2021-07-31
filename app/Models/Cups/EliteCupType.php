<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Distance;
use App\Models\Group;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EliteCupType extends AbstractCupType
{
    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getName(): string
    {
        return 'Elite';
    }

    /**
     * @param CupEvent $cupEvent
     * @param Group $mainGroup
     * @return array<int, CupEventPoint>|Collection
     */
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection
    {
        $cupEventProtocolLines = $this->getProtocolLines($cupEvent, $mainGroup);
        $results = $this->calculateLines($cupEvent, $cupEventProtocolLines);

        return $results->sortByDesc(function (CupEventPoint $cupEventResult) {
            return $cupEventResult->points;
        });
    }

    private function getProtocolLines(CupEvent $cupEvent, Group $group): Collection
    {
        $mainDistance = Distance::whereGroupId($group->id)->whereEventId($cupEvent->event_id)->first();
        $groupsName = $group->maleGroups();
        $equalDistances = Distance::selectRaw(DB::raw('distances.id'))
            ->whereEventId($cupEvent->event_id)
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('groups.name', $groupsName)
            ->whereLength($mainDistance->length)
            ->wherePoints($mainDistance->points)
            ->get();

        $distances = $equalDistances->add($mainDistance);

        return ProtocolLine::whereIn('distance_id', $distances->pluck('id')->unique())->get();
    }
}
