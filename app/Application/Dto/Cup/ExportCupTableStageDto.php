<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ExportCupTableStageDto
{
    public function __construct(
        public int $stageId,
        public string $date,
    ) {
    }
}
