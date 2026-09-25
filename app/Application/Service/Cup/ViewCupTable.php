<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableSearchDto;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;

final class ViewCupTable
{
    public function __construct(
        public readonly string $cupId,
        private readonly string $groupId,
        private readonly CupTableSearchDto $search,
    ) {
    }

    public CupGroup $group {
        get => CupGroupFactory::fromId($this->groupId);
    }

    public ?string $name {
        get => $this->search->name  |> mb_strtolower(...);
    }

}
