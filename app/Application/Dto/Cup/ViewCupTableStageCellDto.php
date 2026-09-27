<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ViewCupTableStageCellDto
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
