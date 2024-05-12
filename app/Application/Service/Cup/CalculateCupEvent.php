<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;

final readonly class CalculateCupEvent
{
    public function __construct(
        private string $cupId,
        private string $cupEventId,
        private string $groupId,
    ) {
    }

    public function cupId(): int
    {
        return (int) $this->cupId;
    }

    public function eventId(): int
    {
        return (int) $this->cupEventId;
    }

    public function cupGroup(): CupGroup
    {
        return CupGroupFactory::fromId($this->groupId);
    }
}
