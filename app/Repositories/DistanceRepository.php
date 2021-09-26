<?php

namespace App\Repositories;

use App\Models\CupEvent;
use App\Models\Distance;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class DistanceRepository
{
    public function findDistance(int $groupId, int $eventId): ?Distance
    {
        return Distance::whereGroupId($groupId)->whereEventId($eventId)->first();
    }

    public function getEqualDistances(Distance $distance): Collection
    {
       return Distance::selectRaw(new Expression('distances.*'))
            ->whereEventId($distance->event_id)
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('groups.name', $distance->group->maleGroups())
            ->where('distances.id', '!=', $distance->id)
            ->whereLength($distance->length)
            ->wherePoints($distance->points)
            ->get();
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, Collection $groupNames): Collection
    {
        return Distance::selectRaw(new Expression('distances.*'))
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('group_id', $groups)
            ->whereIn('groups.name', $groupNames)
            ->whereEventId($cupEvent->event_id)
            ->get();
    }
}
