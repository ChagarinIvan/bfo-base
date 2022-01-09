<?php

namespace App\Models\Group;

use Illuminate\Support\Collection;

class CupGroupFactory
{
    /**
     * @param GroupAge[] $ages
     * @return Collection|CupGroup[]
     */
    public static function getAgeTypeGroups(array $ages = []): Collection
    {
        $groups = Collection::make();

        if (count($ages) > 0) {
            foreach ($ages as $age) {
                $groups->push(new CupGroup(GroupMale::Man, $age));
                $groups->push(new CupGroup(GroupMale::Woman, $age));
            }
        } else {
            $groups->push(new CupGroup(GroupMale::Man));
            $groups->push(new CupGroup(GroupMale::Woman));
        }

        return $groups;
    }

    public static function fromId(string $id): CupGroup
    {
        if (preg_match('#([^\d])_(\d+)#', $id, $m)) {
            return new CupGroup(GroupMale::from($m[1]), ((int)$m[2] > 0) ? GroupAge::from((int)$m[2]) : null);
        }

        throw new \RuntimeException('Wrong group');
    }
}
