<?php

namespace App\Repositories;

use App\Models\Distance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DistanceRepository
{
    public function findDistance(int $groupId, int $eventId): ?Distance
    {
        return Distance::whereGroupId($groupId)->whereEventId($eventId)->first();
    }

    public function getEqualDistances(Distance $distance): Collection
    {
       $distancesIds = Distance::selectRaw(DB::raw('distances.id'))
            ->whereEventId($distance->event_id)
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('groups.name', $distance->group->maleGroups())
            ->where('distances.id', '!=', $distance->id)
            ->whereLength($distance->length)
            ->wherePoints($distance->points)
            ->get();

       return Distance::whereIn('id', $distancesIds)->get();
    }
}
