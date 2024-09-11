<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

final readonly class ViewRankDto
{
    public function __construct(
        public string $id,
        public string $rank,
        public string $startDate,
        public string $personId,
    ) {
    }
}
