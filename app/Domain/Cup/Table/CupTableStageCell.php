<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

final readonly class CupTableStageCell
{
    public function __construct(
        public int $stageId,
        public string $points,
        public bool $counted,
        public string $distanceId,
        public string $protocolLineId,
    ) {
    }
}
