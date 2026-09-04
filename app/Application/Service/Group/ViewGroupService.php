<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Dto\Group\GroupAssembler;
use App\Application\Dto\Group\ViewGroupDto;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Group\GroupRepository;

final readonly class ViewGroupService
{
    public function __construct(private GroupRepository $groups, private GroupAssembler $assembler)
    {
    }

    public function execute(int $id): ViewGroupDto
    {
        $group = $this->groups->byId($id) ?? throw new GroupNotFound();

        return $this->assembler->toViewGroupDto($group);
    }
}
