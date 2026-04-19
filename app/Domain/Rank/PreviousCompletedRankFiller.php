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

    public function fill(int $personId, ?Rank $rank, ?Carbon $date = null): ?Rank
    {
        $now = $this->clock->now();
        if ($date === null) {
            $date = $now;
        }

        $finishDate = $rank?->finish_date;
//        dump($finishDate < $date);

        if (!$finishDate) {
            $finishDate = $date;
        }

        if ($finishDate <= $date) {
            // тут трэба узять протокол лініі за 2 года, дзе было выкананне адсартырованные па моцы разраду
            $criteria = new Criteria(
                [
                    'personId' => $personId,
                    'dateFrom' => $finishDate->clone()->addYears(-2),
                    'dateTo' => $finishDate,
                    'completedRank' => true,
                ],
                ['completedRank' => 'desc', 'eventDate' => 'asc'],
            );
//            dump($criteria);
            $protocolLines = $this->protocolLines->byCriteria($criteria);
//            dump($protocolLines->count());

//            dump('$protocolLines->count(): ' . $protocolLines->count());
            if ($protocolLines->isEmpty()) {
                return null;
            }

            if ($rank) {
                $protocolLines = $protocolLines->filter(static fn (ProtocolLine $pl): bool => $pl->complete_rank !== $rank->rank);
            }

            /** @var ProtocolLine $first */
            $first = $protocolLines->first();
//            dump($rank->rank);
//            dump($first->complete_rank);
            $protocolLines = $protocolLines->filter(static fn (ProtocolLine $pl): bool => $pl->complete_rank === $first->complete_rank);
//            dump('$protocolLines->count(): ' . $protocolLines->count());

            if (!$protocolLines->count()) {
                return null;
            }

            $previous = $this->ranks->oneByCriteria(
                new Criteria([
                    'person_id' => $personId,
                    'activated' => true,
                    'rank' => $first->complete_rank,
                ], ['events.date' => 'asc'])
            );
            $activationDate = null;

            foreach ($protocolLines as $protocolLine) {
                /** @var ProtocolLine $protocolLine */
                if (!$activationDate) {
                    if ($previous) {
                        $activationDate = $previous->activated_date;
                    } else {
                        $activationDate = $protocolLine->activate_rank;
                    }
                }

                $newRank = $this->factory->create($this->createRankInput(
                    protocolLine: $protocolLine,
                    activatedDate: $activationDate,
                ));

//                dump('Rank', $newRank);

                if (!$this->juniorRankAgeValidator->validate($newRank->person_id, $newRank->rank, Year::actualYear())) {
                    continue;
                }

                $this->ranks->add($newRank);

//                dump('event date ' . $protocolLine->distance->event->date->toDateString());
//                dump('activation date ' . $newRank->activated_date->toDateString());
//                dump('finish date ' . $newRank->finish_date->toDateString());
//                dump('start date ' . $newRank->start_date->toDateString());

                // трэба абнавіць усе папярэднія разряды
                $this->updater->update(
                    personId: $newRank->person_id,
                    rank: $newRank->rank,
                    startDate: $newRank->start_date,
                    finishDate: $newRank->finish_date,
                );

//                dump('Updated');
            }

            return $newRank ?? null;
        }

        return $rank;
    }

    private function createRankInput(ProtocolLine $protocolLine, ?Carbon $activatedDate): RankInput
    {
        return new RankInput(
            personId: $protocolLine->person_id,
            eventId: $protocolLine->distance->event_id,
            rank: $protocolLine->complete_rank,
            startDate: $protocolLine->event->date->clone(),
            activatedDate: $activatedDate,
            finishDate: $protocolLine->event->date->clone()->addYears(2),
        );
    }
}
