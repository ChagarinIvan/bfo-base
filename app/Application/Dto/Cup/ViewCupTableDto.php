<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

final readonly class ViewCupTableDto
{
    /** @param list<ViewCupTableStageDto> $stages
     * @param list<ViewCupTableRowDto> $rows */
    public function __construct(
        public array $stages,
        public array $rows,
    ) {
    }
}
