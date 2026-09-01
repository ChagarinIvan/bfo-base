<?php

declare(strict_types=1);

namespace App\Domain\Club;

final readonly class ClubInfo
{
    public function __construct(
        public string $name,
        public string $normalizeName,
    ) {
    }
}
