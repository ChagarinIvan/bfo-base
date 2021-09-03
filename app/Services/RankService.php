<?php

declare(strict_types=1);

namespace App\Services;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Repositories\RanksRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RankService
{
    private RanksRepository $ranksRepository;

    public function __construct(RanksRepository $ranksRepository)
    {
        $this->ranksRepository = $ranksRepository;
    }

    public const RANKS_POWER = [
        Rank::WITHOUT_RANK => 0,
        Rank::UNIOR_THIRD_RANK => 1,
        Rank::UNIOR_SECOND_RANK => 2,
        Rank::UNIOR_FIRST_RANK => 3,
        Rank::THIRD_RANK => 4,
        Rank::SECOND_RANK => 5,
        Rank::FIRST_RANK => 6,
        Rank::SMC_RANK => 7,
        Rank::SM_RANK => 8,
        Rank::WSM_RANK => 9,
    ];

    public function getPersonRanks(int $personId): RanksCollection
    {
        $filter = new RanksFilter();
        $filter->personId = $personId;
        $filter->isOrderDescByFinishDateAnd = true;
        return $this->ranksRepository->getRanksList($filter);
    }

    public function getFinishedRanks(string $rank): RanksCollection
    {
        $filter = new RanksFilter();
        $filter->rank = $rank;
        $filter->with = ['person', 'event'];
        $filter->isOrderByFinish = true;
        $filter->date = Carbon::now();
        $ranks = $this->ranksRepository->getRanksList($filter);
        $ranks->groupByPerson();
        $ranks->transform(fn(Collection $ranks) => $ranks->first());
        return $ranks;
    }

    public function getActualRank(int $personId): ?Rank
    {
        $rank = $this->ranksRepository->getLatestRank($personId);
        if ($rank !== null) {
            $rank = $this->createPreviousRank($rank);
        }
        return $rank;
    }

    public function fillRank(ProtocolLine $protocolLine): void
    {
        if (!Rank::validateRank($protocolLine->complete_rank)) {
            return;
        }

        $event = $protocolLine->event;
        $actualRank = $this->getActualRank($protocolLine->person_id);
        if ($actualRank) {
            if ($actualRank->rank === $protocolLine->complete_rank) {
                $actualRank = $actualRank->replicate();
                $actualRank->event_id = $event->id;
                $actualRank->finish_date = $event->date->clone()->addYears(2);
            } elseif (self::RANKS_POWER[$protocolLine->complete_rank] > self::RANKS_POWER[$actualRank->rank]) {
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = $actualRank->person_id;
                $ranksFilter->rank = $actualRank->rank;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);
                $finishDate = $event->date->clone()->addDays(-1);

                $ranks->each(function (Rank $rank) use ($finishDate) {
                    $rank->finish_date = $finishDate;
                    $this->ranksRepository->storeRank($rank);
                });

                $actualRank = $this->createNewRank($protocolLine);
            } else {
                return;
            }
        } else {
            $actualRank = $this->createNewRank($protocolLine);
        }
        $this->ranksRepository->storeRank($actualRank);
    }

    //еслі разряд просрочілся то создаётся новый с более низким разрядом
    private function createPreviousRank(Rank $rank): ?Rank
    {
        if ($rank->finish_date < Carbon::now()) {
            if (!isset(Rank::PREVIOUS_RANKS[$rank->rank])) {
                return null;
            };
            $rank = $rank->replicate();
            $rank->start_date = $rank->finish_date->addDay();
            $rank->finish_date = $rank->start_date->addYears(2);
            $rank->event_id = null;
            $rank->rank = Rank::PREVIOUS_RANKS[$rank->rank];
            $rank = $this->ranksRepository->storeRank($rank);
            return $this->createPreviousRank($rank);
        }
        return $rank;
    }

    private function createNewRank(ProtocolLine $protocolLine): Rank
    {
        $lastRank = new Rank();
        $lastRank->person_id = $protocolLine->person_id;
        $lastRank->event_id = $protocolLine->event->id;
        $lastRank->rank = $protocolLine->complete_rank;
        $lastRank->start_date = $protocolLine->event->date;
        $lastRank->finish_date = $protocolLine->event->date->clone()->addYears(2);
        return $lastRank;
    }

    public function cleanAll(): void
    {
        $this->ranksRepository->cleanAll();
    }
}
