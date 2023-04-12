<?php

namespace App\Services;

use App\Collections\RanksCollection;
use App\Filters\RanksFilter;
use App\Models\Event;
use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Models\Year;
use App\Repositories\RanksRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RankService
{
    public function __construct(
        private readonly RanksRepository $ranksRepository,
        private readonly ProtocolLineService $protocolLineService,
        private readonly PersonsService $personsService
    ) {}

    public const RANKS_POWER = [
        Rank::WITHOUT_RANK => 0,
        Rank::JUNIOR_THIRD_RANK => 1,
        Rank::JUNIOR_SECOND_RANK => 2,
        Rank::JUNIOR_FIRST_RANK => 3,
        Rank::THIRD_RANK => 4,
        Rank::SECOND_RANK => 5,
        Rank::FIRST_RANK => 6,
        Rank::SMC_RANK => 7,
        Rank::SM_RANK => 8,
        Rank::WSM_RANK => 9,
    ];

    /**
     * @return RanksCollection
     */
    public function getPersonRanks(int $personId): Collection
    {
        $filter = new RanksFilter();
        $filter->personId = $personId;
        $filter->isOrderDescByFinishDate = true;
        return $this->ranksRepository->getRanksList($filter);
    }

    public function reFillRanksForPerson(int $personId): void
    {
        $ranks = $this->getPersonRanks($personId);
        $this->ranksRepository->deleteRanks($ranks);

        foreach ($this->protocolLineService->getPersonProtocolLines($personId) as $protocolLine) {
            $this->fillRank($protocolLine);
        }
    }

    public function getFinishedRanks(string $rank): RanksCollection
    {
        $filter = new RanksFilter();
        $filter->rank = $rank;
        $filter->with = ['person', 'event'];
        $nowDate = Carbon::now();
        $filter->startDateLess = $nowDate;
        $filter->finishDateMore = $nowDate;
        $ranks = $this->ranksRepository->getRanksList($filter);
        $filter->finishDateMore = null;
        $filter->finishDateLess = $nowDate;

        if (isset(Rank::NEXT_RANKS[$rank])) {
            $filter->rank = Rank::NEXT_RANKS[$rank];
            $filter->haveNoNextRank = true;
            $previousRanks = $this->ranksRepository->getRanksList($filter);
            $ranks = $ranks->merge($previousRanks);
        }

        $personsIds = $ranks->groupBy('person_id')->keys();
        if ($rank === Rank::JUNIOR_THIRD_RANK) {
            $personsIds = $personsIds->merge($this->ranksRepository->getPersonsIdsWithoutRanks()->pluck('id'));
        }
        $ranks = RanksCollection::empty();

        foreach ($personsIds as $personId) {
            $actualRank = $this->getActualRank($personId, $nowDate);
            if ($actualRank && $actualRank->rank === $rank)
            $ranks->put($personId, $actualRank);
        }

        return $ranks;
    }

    public function getActualRank(int $personId, Carbon $date = null): ?Rank
    {
        $rank = $this->ranksRepository->getDateRank($personId, $date);
        if ($rank === null) {
            $rank = $this->ranksRepository->getDateRank($personId);
        }

        if ($rank === null) {
            $rank = $this->checkThirdRank($personId);
        }

        if ($rank !== null) {
            $rank = $this->createPreviousRank($rank, $date);
        }

        return $rank;
    }

    public function getActualRanks(Collection $personIds): Collection
    {
        $actualRanks = new Collection();
        foreach ($personIds as $personId) {
            $rank = $this->getActualRank($personId);
            if ($rank) {
                $actualRanks->put($personId, $rank);
            }
        }

        return $actualRanks;
    }

    /**
     * Надо добавить логику когда идёт добавление в середину имеющихся разрядов, с исправлением уже имеющихся
     *
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
                    $protocolLine = $this->protocolLineService->getProtocolLineWithEvent($protocolLineId);
                    if ($protocolLine) {
                        $protocolLines->push($protocolLine);
                    }
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

            if (!$this->checkMaxJuniorAge($rank->person_id, $rank->rank)) {
                return null;
            }

            $rank = $this->ranksRepository->storeRank($rank);

            return $this->createPreviousRank($rank, $date);
        }

        return $rank;
    }

    private function checkMaxJuniorAge(int $personId, string $rank): bool
    {
        if (!in_array($rank, Rank::JUNIOR_RANKS, true)) {
            return true;
        }

        $person = $this->personsService->getPerson($personId);
        $age = Year::actualYear()->value - $person->birthday?->year;

        return $age <= Rank::MAX_JUNIOR_AGE;
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

    public function deleteEventRanks(Event $event): void
    {
        $event->ranks()->delete();
    }

    public function storeRank(Rank $rank): void
    {
        $rank->save();
    }

    /**
     * Присвоение 3ю разряда за 3 успешных старта
     */
    private function checkThirdRank(int $personId): ?Rank
    {
        foreach(Year::cases() as $year) {
            if (!$this->checkMaxJuniorAge($personId, Rank::JUNIOR_THIRD_RANK)) {
                continue;
            }

            $results = $this->protocolLineService->getPersonProtocolLines($personId, $year);
            $results = $results->filter(fn(ProtocolLine $line) => $line->time !== null && !$line->vk);
            if ($results->count() >= 3) {
                $results = $results
                    ->sortBy(fn(ProtocolLine $line) => $line->event->date)
                    ->slice(0, 3)
                    ->values()
                ;
                $rank = $this->createNewRank($results->get(2));
                $rank->rank = Rank::JUNIOR_THIRD_RANK;
                $rank->save();

                return $rank;
            }
        }

        return null;
    }
}
