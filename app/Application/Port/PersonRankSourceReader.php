<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Application\Dto\Person\PersonRankSourceDto;

interface PersonRankSourceReader
{
    public function byHistoryId(int $historyId): ?PersonRankSourceDto;
}
