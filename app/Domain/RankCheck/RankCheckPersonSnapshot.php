<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

final readonly class RankCheckPersonSnapshot
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $club,
        public ?string $rank,
        public ?string $year,
    ) {
    }
}
