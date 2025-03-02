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

    public function criteriaWithDate(): Criteria
    {
        return new Criteria(['person_id' => $this->personId, 'activated' => true, 'date' => $this->date]);
    }

    public function criteriaWithoutDate(): Criteria
    {
        return new Criteria(['person_id' => $this->personId, 'activated' => true, 'startDateLess' => $this->date]);
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
