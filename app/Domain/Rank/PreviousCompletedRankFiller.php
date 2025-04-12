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
            dump('$rank->start_date: ' . $rank->start_date);
            dump('$finishDate: ' . $finishDate);
            $protocolLines = $this->protocolLines->byCriteria(new Criteria(
                [
                    'personId' => $rank->person_id,
                    'dateFrom' => $finishDate->addYears(-2),
                    'dateTo' => $finishDate,
                    'completedRank' => true,
                ],
                ['completedRank' => 'desc', 'eventDate' => 'asc'],
            ));

            dump('$protocolLines->count(): ' . $protocolLines->count());
            if ($protocolLines->isEmpty()) {
                return null;
            }

            $first = $protocolLines->first();
            $protocolLines = $protocolLines->filter(fn (ProtocolLine $pl) => $pl->complete_rank === $first->complete_rank);
            dump('$protocolLines->count(): ' . $protocolLines->count());

            $startDate = $finishDate->addDay();
            foreach ($protocolLines as $protocolLine) {
                $newRank = $this->factory->create($this->createRankInput($protocolLine, $startDate));

                if (!$this->juniorRankAgeValidator->validate($newRank->person_id, $newRank->rank, Year::actualYear())) {
                    continue;
                }

                $this->ranks->add($newRank);

                dump('event date ' . $protocolLine->distance->event->date->toDateString());
                dump('activation date ' . $newRank->activated_date->toDateString());
                dump('finish date ' . $newRank->finish_date->toDateString());
                dump('start date ' . $newRank->start_date->toDateString());

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
