<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Shared\Criteria;

final readonly class PersonRanks
{
    public function __construct(private string $personId)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(['person_id' => $this->personId]);
    }
}
