<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

final readonly class RefillPersonRanks
{
    public function __construct(private string $personId)
    {
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }
}
