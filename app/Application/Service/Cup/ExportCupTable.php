<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

final readonly class ExportCupTable
{
    public function __construct(private string $cupId)
    {
    }

    public function cupId(): int
    {
        return (int) $this->cupId;
    }
}
