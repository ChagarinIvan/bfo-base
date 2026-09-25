<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Domain\Cup\Group\CupGroup;

final readonly class ViewCupTable
{
    public function __construct(
        public string $cupId,
        public CupGroup $group,
    ) {
    }
}
