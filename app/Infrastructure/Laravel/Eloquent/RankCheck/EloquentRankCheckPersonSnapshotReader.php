<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\RankCheck;

use App\Domain\Person\Person;
use App\Domain\RankCheck\RankCheckPersonSnapshot;
use App\Domain\RankCheck\RankCheckPersonSnapshotReader;
use function trim;

final class EloquentRankCheckPersonSnapshotReader implements RankCheckPersonSnapshotReader
{
    public function read(array $personIds): array
    {
        if ($personIds === []) {
            return [];
        }

        return Person::query()
            ->with('club')
            ->whereIn('id', $personIds)
            ->get()
            ->mapWithKeys(static fn(Person $person): array => [$person->id => new RankCheckPersonSnapshot(
                id: $person->id,
                name: trim($person->lastname . ' ' . $person->firstname),
                club: $person->club?->name,
                rank: $person->current_rank->label(),
                year: $person->birthday?->format('Y'),
            )])
            ->all()
        ;
    }
}
