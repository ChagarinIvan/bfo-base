<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use function count;

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

    public function getAll(array $with = []): Collection
    {
        return count($with) > 0 ? Group::with($with)->get() : Group::all();
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
