<?php

declare(strict_types=1);

namespace App\Domain\Group;

interface GroupUpdater
{
    public function update(Group $group, GroupInput $input): Group;
}
