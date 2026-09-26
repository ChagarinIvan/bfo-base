<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

final readonly class CupTable
{
    /**
     * @param list<CupTableStage> $stages
     * @param list<CupTableRow> $rows
     */
    public function __construct(
        public array $stages,
        public array $rows,
    ) {
    }

    /** @param list<CupTableRow> $rows */
    public function withRows(array $rows): self
    {
        return new self($this->stages, $rows);
    }
}
