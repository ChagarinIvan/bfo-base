<?php

declare(strict_types=1);

namespace App\Application\Service\Year;

use App\Models\Year;
use function array_map;

final readonly class ListYears
{
    /** @return list<int> */
    public function execute(): array
    {
        return array_map(
            static fn (Year $year): int => $year->value,
            Year::cases(),
        );
    }
}
