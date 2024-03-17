<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

final readonly class ClubInput
{
    public function __construct(
        public string $name,
        public int $userId,
    ) {
    }
}
