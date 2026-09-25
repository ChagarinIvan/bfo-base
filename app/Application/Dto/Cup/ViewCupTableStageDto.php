<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ViewCupTableStageDto
{
    public function __construct(
        public int $stageId,
        public string $eventId,
        public string $date,
        public string $name,
    ) {
    }
}
