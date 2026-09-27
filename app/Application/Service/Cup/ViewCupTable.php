<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableSearchDto;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use function mb_strtolower;

final class ViewCupTable
{
    public CupGroup $group {
        get => CupGroupFactory::fromId($this->groupId);
    }

    public ?string $name {
        get => $this->search->name === null ? null : mb_strtolower($this->search->name);
    }

    public function __construct(
        public readonly string $cupId,
        private readonly string $groupId,
        private readonly CupTableSearchDto $search,
    ) {
    }
}
