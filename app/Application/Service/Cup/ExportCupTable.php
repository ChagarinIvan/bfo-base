<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;

final readonly class ExportCupTable
{
    public function __construct(private string $cupId, private ?string $groupId = null)
    {
    }

    public function cupId(): int
    {
        return (int) $this->cupId;
    }

    public function group(): ?CupGroup
    {
        return $this->groupId === null ? null : CupGroupFactory::fromId($this->groupId);
    }
}
