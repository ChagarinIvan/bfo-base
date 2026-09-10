<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Distance;

use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use function implode;
use function str_contains;

final readonly class EloquentDistanceRepository implements DistanceRepository
{
    /** @return Collection<int, Distance> */
    public function byCriteria(Criteria $criteria): Collection
    {
        return $this->buildQuery($criteria)
            ->with('group')
            ->orderBy('groups.name')
            ->orderBy('distances.id')
            ->get()
        ;
    }

    public function oneByCriteria(Criteria $criteria): ?Distance
    {
        return $this->buildQuery($criteria)->with('group')->first();
    }

    /** @return Builder<Distance> */
    private function buildQuery(Criteria $criteria): Builder
    {
        $query = Distance::query()
            ->select('distances.*')
            ->join('events', 'events.id', '=', 'distances.event_id')
            ->join('groups', 'groups.id', '=', 'distances.group_id')
            ->where('events.active', true)
        ;

        if ($criteria->hasParam('eventId')) {
            $query->where('distances.event_id', $criteria->param('eventId'));
        }

        if ($criteria->hasParam('id')) {
            $query->where('distances.id', $criteria->param('id'));
        }

        if ($criteria->hasParam('groupId')) {
            $query->where('distances.group_id', $criteria->param('groupId'));
        }

        if ($criteria->hasParam('groupIds')) {
            $query->whereIn('distances.group_id', $criteria->param('groupIds'));
        }

        if ($criteria->hasParam('groupNames')) {
            $groupNames = $criteria->param('groupNames');

            if (str_contains((string) implode('', $groupNames), '%')) {
                $query->where(static function (Builder $query) use ($groupNames): void {
                    foreach ($groupNames as $name) {
                        $query->orWhere('groups.name', 'like', $name);
                    }
                });
            } else {
                $query->whereIn('groups.name', $groupNames);
            }
        }

        if ($criteria->hasParam('length')) {
            $query->where('distances.length', $criteria->param('length'));
        }

        if ($criteria->hasParam('points')) {
            $query->where('distances.points', $criteria->param('points'));
        }

        if ($criteria->hasParam('excludeId')) {
            $query->where('distances.id', '!=', $criteria->param('excludeId'));
        }

        return $query;
    }
}
