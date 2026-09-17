<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\Rank\Rank;

final readonly class RankListItem
{
    public function __construct(
        public ?string $group,
        public string $name,
        public string $lastname,
        public string $firstname,
        public ?string $club,
        public Rank $rank,
        public ?string $number,
        public ?int $year,
    ) {
    }
}
