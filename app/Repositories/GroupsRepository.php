<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

class GroupsRepository
{
    public function getGroup(int $id): ?Group
    {
        return Group::find($id);
    }

    public function getEventGroups(int $eventId): Collection
    {
        return Group::selectRaw(new Expression('`groups`.*'))
            ->join('distances', 'distances.group_id', '=', 'groups.id')
            ->where('distances.event_id', '=', $eventId)
            ->get();
    }

    public function getAll(): Collection
    {
        return Group::all();
    }

    public function searchGroup(string $query): ?Group
    {
        return Group::whereName($query)->first();
    }

    public function storeGroup(Group $group): Group
    {
        $group->save();
        return $group;
    }
}
