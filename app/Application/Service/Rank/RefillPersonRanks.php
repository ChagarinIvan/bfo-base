<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Shared\Criteria;

final readonly class RefillPersonRanks
{
    public function __construct(private string $personId)
    {
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }

    public function criteria(): Criteria
    {
        return new Criteria(['personId' => $this->personId]);
    }
}
