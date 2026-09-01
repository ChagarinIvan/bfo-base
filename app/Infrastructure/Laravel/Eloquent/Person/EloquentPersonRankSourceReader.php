<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Application\Dto\Person\PersonRankSourceDto;
use App\Application\Port\PersonRankSourceReader;

final readonly class EloquentPersonRankSourceReader implements PersonRankSourceReader
{
    public function byHistoryId(int $historyId): ?PersonRankSourceDto
    {
        $source = PersonRankHistoryRecord::query()->find($historyId);

        return $source === null
            ? null
            : new PersonRankSourceDto((int) $source->person_id, (int) $source->protocol_line_id);
    }
}
