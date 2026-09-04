<?php

declare(strict_types=1);

namespace App\Domain\Group;

final readonly class GroupInput
{
    public function __construct(public GroupInfo $info, public int $userId)
    {
    }
}
