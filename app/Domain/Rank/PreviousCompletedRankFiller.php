<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use Carbon\Carbon;

final readonly class PreviousCompletedRankFiller
{
    public function __construct(
        private Clock $clock,
        private RankFactory $factory,
        private RankRepository $ranks,
        private JuniorRankAgeValidator $juniorRankAgeValidator,
    ) {
    }

    public function fill(Rank $rank, ?Carbon $date = null): ?Rank
    {
        if ($date === null) {
            $date = $this->clock->now();
        }

        if ($rank->finish_date < $date) {
            $rank = $this->ranks->oneByCriteria(new Criteria([
                'person_id' => $rank->person_id,
                'activated' => true,
                'finish_date_to' => $rank->start_date,
            ]));

            if (!$rank) {
                return null;
            }

            $newRank = $this->factory->create($this->createRankInput($rank));

            if (!$this->juniorRankAgeValidator->validate($newRank->person_id, $newRank->rank, Year::actualYear())) {
                return null;
            }

            $this->ranks->add($newRank);

            return $this->fill($newRank, $date);
        }

        return $rank;
    }

    private function createRankInput(Rank $rank): RankInput
    {
        $startDate = $rank->finish_date->addDay();

        return new RankInput(
            personId: $rank->person_id,
            eventId: null,
            rank: $rank->rank,
            startDate: $startDate,
            activatedDate:$startDate,
        );
    }
}
