<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Application\Dto\Person\PersonRankHistoryDto;

interface PersonRankHistoryReader
{
    public function byId(int $historyId): ?PersonRankHistoryDto;

    /** @return list<PersonRankHistoryDto> */
    public function forPerson(int $personId): array;
}
