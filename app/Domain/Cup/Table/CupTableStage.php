<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

final readonly class CupTableStage
{
    public function __construct(
        public int $stageId,
        public string $eventId,
        public string $date,
        public string $name,
    ) {
    }
}
