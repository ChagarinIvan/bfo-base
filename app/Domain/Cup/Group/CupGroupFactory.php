<?php

declare(strict_types=1);

namespace App\Domain\Cup\Group;

use App\Application\Service\Group\Exception\GroupNotFound;
use Illuminate\Support\Collection;
use function count;
use function preg_match;

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

    /**
     * @throws GroupNotFound
     */
    public static function fromId(string $id): CupGroup
    {
        if (preg_match('#(\D)_(\d+)_(.*)#', $id, $m)) {
            return new CupGroup(GroupMale::from($m[1]), ((int)$m[2] > 0) ? GroupAge::from((int)$m[2]) : null, ($m[3] === '') ? null : $m[3]);
        }

        throw new GroupNotFound('Wrong group');
    }
}
