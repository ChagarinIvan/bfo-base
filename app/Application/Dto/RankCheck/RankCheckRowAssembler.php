<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

use App\Domain\RankCheck\RankCheckRow;

final readonly class RankCheckRowAssembler
{
    public function toDto(RankCheckRow $row): RankCheckRowDto
    {
        return new RankCheckRowDto(
            position: $row->position,
            group: $row->group,
            name: $row->name,
            club: $row->club,
            rank: $row->rank,
            number: $row->number,
            year: $row->year,
            personId: $row->person_id === null ? null : (string) $row->person_id,
            databaseName: $row->database_name,
            databaseClub: $row->database_club,
            databaseRank: $row->database_rank,
            databaseYear: $row->database_year,
            hasPerson: $row->has_person,
            isEqual: $row->is_equal,
        );
    }
}
