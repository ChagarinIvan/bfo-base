<?php

declare(strict_types=1);

namespace App\Domain\Cup;

use App\Models\Year;

final readonly class CupInfo
{
    public function __construct(
        public string $name,
        public int $eventsCount,
        public Year $year,
        public CupType $type,
    ) {
    }
}
