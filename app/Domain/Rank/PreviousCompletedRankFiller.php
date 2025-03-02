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
    ) {
    }

    public function fill(Rank $rank, ?Carbon $date = null): ?Rank
    {
        if ($date === null) {
            $date = $this->clock->now();
        }

        $finishDate = $rank->finish_date;

        if ($finishDate < $date) {
            // тут трэба узять протокол лініі за 2 года, дзе было выкананне разряда меньш чым папярэдні
            $protocolLine = $this->protocolLines->oneByCriteria(new Criteria(
                [
                    'personId' => $rank->person_id,
                    'dateFrom' => $rank->start_date,
                    'dateTo' => $finishDate,
                    'completedRank' => true,
                ],
                ['completedRank' => 'desc'],
            ));

            dump($protocolLine);
            if (!$protocolLine) {
                return null;
            }

            $newRank = $this->factory->create($this->createRankInput($protocolLine, $finishDate->addDay()));

            if (!$this->juniorRankAgeValidator->validate($newRank->person_id, $newRank->rank, Year::actualYear())) {
                return null;
            }

            dump('PreviousCompletedRankFiller:fill');
            $this->ranks->add($newRank);

            return $newRank;
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
