<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\PersonRankHistoryDto;
use App\Application\Port\PersonRankHistoryReader;

final readonly class ListPersonRankHistory
{
    public function __construct(private PersonRankHistoryReader $reader)
    {
    }

    /** @return list<PersonRankHistoryDto> */
    public function execute(int $personId): array
    {
        return $this->reader->forPerson($personId);
    }
}
