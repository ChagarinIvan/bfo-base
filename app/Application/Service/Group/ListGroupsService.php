<?php

declare(strict_types=1);

namespace App\Application\Service\Group;

use App\Application\Dto\Group\GroupAssembler;
use App\Application\Dto\Group\ViewGroupDto;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListGroupsService
{
    public function __construct(private GroupRepository $groups, private GroupAssembler $assembler)
    {
    }

    /** @return Slice<ViewGroupDto> */
    public function execute(ListGroups $command): Slice
    {
        return $this->groups->paginate($command->criteria())->map($this->assembler->toViewGroupDto(...));
    }
}
