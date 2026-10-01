<?php

declare(strict_types=1);

namespace App\Domain\Group;

interface GroupFactory
{
    public function create(GroupInput $input): Group;
}
