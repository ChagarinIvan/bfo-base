<?php

declare(strict_types=1);

namespace App\Application\Service\Person\PersonRankHistory;

final readonly class ListPersonRankHistory
{
    public function __construct(private string $personId)
    {
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }
}
