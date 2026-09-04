<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;

final readonly class StandardGroupUpdater implements GroupUpdater
{
    public function __construct(private Clock $clock)
    {
    }

    public function update(Group $group, GroupInput $input): Group
    {
        $group->updateName($input->info->name, $input->info->normalizeName, new Impression($this->clock->now(), $input->userId));

        return $group;
    }
}
