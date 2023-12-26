<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final readonly class Footprint
{
    public function __construct(
        public int $userId,
    ) {
    }

    public function toString(): string
    {
        return (string) $this->userId;
    }
}
