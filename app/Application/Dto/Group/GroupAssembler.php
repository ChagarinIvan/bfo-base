<?php

declare(strict_types=1);

namespace App\Application\Dto\Group;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Group\Group;

final readonly class GroupAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewGroupDto(Group $group): ViewGroupDto
    {
        return new ViewGroupDto(
            id: (string) $group->id,
            name: $group->name,
            distancesCount: (int) $group->getAttribute('distances_count'),
            created: $this->authAssembler->toImpressionDto($group->created),
            updated: $this->authAssembler->toImpressionDto($group->updated),
        );
    }
}
