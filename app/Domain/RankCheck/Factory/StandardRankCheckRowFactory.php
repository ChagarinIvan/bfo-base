<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Factory;

use App\Domain\Rank\Rank;
use App\Domain\RankCheck\RankCheckRow;

final readonly class StandardRankCheckRowFactory implements RankCheckRowFactory
{
    public function create(RankCheckRowInput $input): RankCheckRow
    {
        $line = $input->line;
        $person = $input->person;
        $sourceRank = $line->rank->label();
        $databaseRank = $person?->rank;
        $isEqual = $person !== null
            && $person->name === $line->name
            && $line->club === $person->club
            && $this->yearsEqual($line->year, $person->year)
            && ($databaseRank ?? Rank::WithoutRank->label()) === $sourceRank;

        $row = new RankCheckRow();
        $row->rank_check_id = $input->rankCheckId;
        $row->position = $input->position;
        $row->group = $line->group;
        $row->name = $line->name;
        $row->club = $line->club;
        $row->rank = $sourceRank;
        $row->number = $line->number;
        $row->year = $line->year === null ? null : (string) $line->year;
        $row->person_id = $person?->id;
        $row->database_name = $person?->name;
        $row->database_club = $person?->club;
        $row->database_rank = $databaseRank;
        $row->database_year = $person?->year;
        $row->has_person = $person !== null;
        $row->is_equal = $isEqual;

        return $row;
    }

    private function yearsEqual(?int $lineYear, ?string $databaseYear): bool
    {
        return $lineYear === null
            ? $databaseYear === null
            : $databaseYear !== null && (int) $databaseYear === $lineYear;
    }
}
