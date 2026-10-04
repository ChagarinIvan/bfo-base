<?php

declare(strict_types=1);

namespace App\Domain\Group;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;

final readonly class StandardGroupFactory implements GroupFactory
{
    public function __construct(
        private Clock $clock,
    )
    {
    }

    public function create(GroupInput $input): Group
    {
        $group = new Group();
        $group->name = $input->info->name;
        $group->normalize_name = $input->info->normalizeName;
        $group->created = new Impression($this->clock->now(), $input->userId);
        $group->updated = new Impression($this->clock->now(), $input->userId);

        return $group;
    }
}
