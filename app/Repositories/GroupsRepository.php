<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Group\Group;
use Illuminate\Support\Collection;
use function count;
use function implode;

class GroupsRepository
{
    /** @var array<string, Collection> */
    private array $searchGroupsCache = [];

    public function getGroup(int $id): ?Group
    {
        return Group::find($id);
    }

    public function getEventGroups(int $eventId): Collection
    {
        return Group::selectRaw('`groups`.*')
            ->join('distances', 'distances.group_id', '=', 'groups.id')
            ->where('distances.event_id', '=', $eventId)
            ->get();
    }

    public function getAll(array $with = []): Collection
    {
        return count($with) > 0 ? Group::with($with)->get() : Group::all();
    }

    public function searchGroup(string $query): ?Group
    {
        return Group::whereName($query)->first();
    }

    /**
     * Расчёт кубка зовёт этот метод с одним и тем же набором имён на каждый этап —
     * мемоизация в рамках запроса убирает повторяющиеся запросы (n+1).
     *
     * @param string[] $names
     * @return Collection|Group[]
     */
    public function searchGroups(array $names): Collection
    {
        return $this->searchGroupsCache[implode('|', $names)] ??= Group::whereIn('name', $names)->get();
    }

    public function storeGroup(Group $group): Group
    {
        $group->save();
        return $group;
    }
}
