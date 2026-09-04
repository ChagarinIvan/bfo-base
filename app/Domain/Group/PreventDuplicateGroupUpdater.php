<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Group\Exception\GroupAlreadyExists;
use App\Domain\Shared\Criteria;

final readonly class PreventDuplicateGroupUpdater implements GroupUpdater
{
    public function __construct(private GroupUpdater $decorated, private GroupRepository $groups)
    {
    }

    public function update(Group $group, GroupInput $input): Group
    {
        $existing = $this->groups->oneByCriteria(new Criteria(['normalizedName' => $input->info->normalizeName]));

        if ($existing instanceof Group && $existing->id !== $group->id) {
            throw new GroupAlreadyExists();
        }

        return $this->decorated->update($group, $input);
    }
}
