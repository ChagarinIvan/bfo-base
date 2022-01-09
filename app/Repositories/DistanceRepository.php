<?php

namespace App\Repositories;

use App\Models\CupEvent;
use App\Models\Distance;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class DistanceRepository
{
    /**
     * @param string[] $groupNames
     * @param int $eventId
     * @return Distance|null
     */
    public function findDistance(array $groupNames, int $eventId): ?Distance
    {
        return Distance::selectRaw(new Expression('distances.*'))
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('groups.name', $groupNames)
            ->whereEventId($eventId)
            ->first();
    }

    /**
     * @param string[] $groupNames
     * @param int $eventId
     * @return Distance[]|Collection
     */
    public function findDistances(array $groupNames, int $eventId): array|Collection
    {
        return Distance::selectRaw(new Expression('distances.*'))
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('groups.name', $groupNames)
            ->whereEventId($eventId)
            ->get();
    }

    public function getEqualDistances(Distance $distance): Collection
    {
       return Distance::selectRaw(new Expression('distances.*'))
            ->whereEventId($distance->event_id)
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->where('distances.id', '!=', $distance->id)
            ->whereLength($distance->length)
            ->wherePoints($distance->points)
            ->get();
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups): Collection
    {
        return Distance::selectRaw(new Expression('distances.*'))
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('group_id', $groups)
            ->whereEventId($cupEvent->event_id)
            ->get();
    }
}
