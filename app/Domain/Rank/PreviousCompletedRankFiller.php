<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
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
        private ProtocolLineRepository $protocolLines,
        private JuniorRankAgeValidator $juniorRankAgeValidator,
        private PreviousRanksFinishDateUpdater $updater,
    ) {
    }

    public function fill(Rank $rank, ?Carbon $date = null): ?Rank
    {
        if ($date === null) {
            $date = $this->clock->now();
        }

        $finishDate = $rank->finish_date;

        if ($finishDate < $date) {
            // тут трэба узять протокол лініі за 2 года, дзе было выкананне адсартырованные па моцы разраду
            $protocolLines = $this->protocolLines->byCriteria(new Criteria(
                [
                    'personId' => $rank->person_id,
                    'dateFrom' => $rank->start_date,
                    'dateTo' => $finishDate,
                    'completedRank' => true,
                ],
                ['completedRank' => 'desc'],
            ));

//            dump('$protocolLines->count(): ' . $protocolLines->count());
            if ($protocolLines->isEmpty()) {
                return null;
            }

            $protocolLines = $protocolLines->groupBy('complete_rank');

            $startDate = $finishDate->addDay();
            foreach ($protocolLines->first() as $protocolLine) {
                $newRank = $this->factory->create($this->createRankInput($protocolLine, $startDate));

                if (!$this->juniorRankAgeValidator->validate($newRank->person_id, $newRank->rank, Year::actualYear())) {
                    continue;
                }

                $this->ranks->add($newRank);

                // трэба абнавіць усе папярэднія разряды
                $this->updater->update(
                    personId: $newRank->person_id,
                    rank: $newRank->rank,
                    startDate: $newRank->start_date,
                    finishDate: $newRank->finish_date,
                );
            }

            return $newRank ?? null;
        }

        return $rank;
    }

    private function createRankInput(ProtocolLine $protocolLine, Carbon $startDate): RankInput
    {
        return new RankInput(
            personId: $protocolLine->person_id,
            eventId: $protocolLine->distance->event_id,
            rank: $protocolLine->complete_rank,
            startDate: $startDate,
            activatedDate: $startDate,
            finishDate: $protocolLine->event->date->clone()->addYears(2),
        );
    }
}
