<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Support\Collection;

class GroupsRepository
{
    public function getGroup(int $id): ?Group
    {
        return Group::find($id);
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
