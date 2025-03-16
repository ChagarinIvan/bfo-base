<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Shared\Criteria;
use Carbon\Carbon;

final readonly class ActivePersonRank
{
    public function __construct(
        private string $personId,
        private ?Carbon $date = null,
    ) {
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }

    public function date(): ?Carbon
    {
        return $this->date;
    }
}
