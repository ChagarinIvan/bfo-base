<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use Illuminate\Support\Collection;
use function implode;
use function str_contains;

class DistanceRepository
{
    /**
     * @param string[] $groupNames
     */
    public function findDistance(array $groupNames, int $eventId): ?Distance
    {
        $query = Distance::selectRaw('distances.*')->join('groups', 'groups.id', '=', 'distances.group_id');

        if (str_contains(implode('', $groupNames), '%')) {
            $query = $query->where(static function ($q) use ($groupNames): void {
                foreach ($groupNames as $name) {
                    $q->orWhere('groups.name', 'like', $name);
                }
            });
        } else {
            $query = $query->whereIn('groups.name', $groupNames);
        }

        return $query
            ->whereEventId($eventId)
            ->first()
        ;
    }

    public function getEqualDistances(Distance $distance): Collection
    {
        return Distance::selectRaw('distances.*')
             ->whereEventId($distance->event_id)
             ->join('groups', 'groups.id', '=', 'distances.group_id')
             ->where('distances.id', '!=', $distance->id)
             ->whereLength($distance->length)
             ->wherePoints($distance->points)
             ->get()
        ;
    }

    public function byId(int $id): ?Distance
    {
        return Distance::find($id);
    }

    public function getEventGroupDistance(int $eventId, int $groupId): ?Distance
    {
        return Distance::whereEventId($eventId)
            ->whereGroupId($groupId)
            ->first()
        ;
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, bool $withEquals = false): Collection
    {
        $distances = Distance::selectRaw('distances.*')
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->whereIn('group_id', $groups)
            ->whereEventId($cupEvent->event_id)
            ->get()
        ;

        if (!$withEquals) {
            return $distances;
        }

        $result = Collection::empty();

        foreach ($distances as $distance) {
            $result->add($distance);
            $result->push(...$this->getEqualDistances($distance)->values());
        }

        return $result->unique();
    }
}
