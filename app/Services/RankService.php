<?php

declare(strict_types=1);

namespace App\Services;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Repositories\RanksRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RankService
{
    private RanksRepository $ranksRepository;
    private ProtocolLineService $protocolLineService;

    public function __construct(RanksRepository $ranksRepository, ProtocolLineService $protocolLineService)
    {
        $this->ranksRepository = $ranksRepository;
        $this->protocolLineService = $protocolLineService;
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
        $filter->startDateLess = Carbon::now();
        $ranks = $this->ranksRepository->getRanksList($filter);
        $ranks->groupByPerson();
        $ranks->transform(fn(Collection $ranks) => $ranks->first());
        return $ranks;
    }

    public function getActualRank(int $personId, Carbon $date = null): ?Rank
    {
        $rank = $this->ranksRepository->getDateRank($personId, $date);
        if ($rank === null) {
            $rank = $this->ranksRepository->getDateRank($personId);
        }

        if ($rank !== null) {
            $rank = $this->createPreviousRank($rank, $date);
        }
        return $rank;
    }

    /**
     * Надо добавить логику когда идёт добавление в середину имеющихся разрядов, с исправлением уже имеющихся
     * @param ProtocolLine $protocolLine
     */
    public function fillRank(ProtocolLine $protocolLine): void
    {
        if (!Rank::validateRank($protocolLine->complete_rank)) {
            return;
        }

        $event = $protocolLine->event;
        $actualRank = $this->getActualRank($protocolLine->person_id, $protocolLine->event->date);

        if ($actualRank) {
            if ($actualRank->rank === $protocolLine->complete_rank) {
                $newRank = $actualRank->replicate();
                $newRank->event_id = $event->id;
                if ($event->date > ($actualRank->event_id === null ? $actualRank->start_date : $actualRank->event->date)) {
                    $newRank->finish_date = $event->date->clone()->addYears(2);
                }
                $this->ranksRepository->storeRank($newRank);
            } elseif (self::RANKS_POWER[$protocolLine->complete_rank] > self::RANKS_POWER[$actualRank->rank]) {
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = $actualRank->person_id;
                $ranksFilter->rank = $actualRank->rank;
                $ranksFilter->startDateLess = $event->date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);
                $finishDate = $event->date->clone()->addDays(-1);

                $ranks->each(function (Rank $rank) use ($finishDate) {
                    $rank->finish_date = $finishDate;
                    $this->ranksRepository->storeRank($rank);
                });

                //Надо взять все разряды которые после этой даты
                //Отсортировать их по дате евента и заново пересохранить
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = $actualRank->person_id;
                $ranksFilter->startDateMore = $event->date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);
                $protocolLines = new Collection();
                $ranks->each(function (Rank $rank) use (&$protocolLines) {
                    $protocolLineId = $this->protocolLineService->getProtocolLineIdForRank($rank);
                    $protocolLines->push($this->protocolLineService->getProtocolLineWithEvent($protocolLineId));
                });
                $this->ranksRepository->deleteRanks($ranks);
                $newRank = $this->createNewRank($protocolLine);
                $this->ranksRepository->storeRank($newRank);

                $protocolLines = $protocolLines->sortBy('distance.event.date');
                foreach ($protocolLines as $protocolLine) {
                    /** @var ProtocolLine $protocolLine */
                    $this->fillRank($protocolLine);
                }
            }
        } else {
            $newRank = $this->createNewRank($protocolLine);
            $this->ranksRepository->storeRank($newRank);
        }
    }

    //еслі разряд просрочілся то создаётся новый с более низким разрядом
    private function createPreviousRank(Rank $rank, Carbon $date = null): ?Rank
    {
        if ($date === null) {
            $date = Carbon::now();
        }
        if ($rank->finish_date < $date) {
            if (!isset(Rank::PREVIOUS_RANKS[$rank->rank])) {
                return null;
            }
            $rank = $rank->replicate();
            $rank->start_date = $rank->finish_date->addDay();
            $rank->finish_date = $rank->start_date->addYears(2);
            $rank->event_id = null;
            $rank->rank = Rank::PREVIOUS_RANKS[$rank->rank];
            $rank = $this->ranksRepository->storeRank($rank);
            return $this->createPreviousRank($rank, $date);
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
