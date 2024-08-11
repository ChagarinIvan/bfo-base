<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Filters\RanksFilter;
use App\Domain\Rank\Rank;
use App\Models\Year;
use App\Repositories\RanksRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use function in_array;

class RankService
{
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

    public function __construct(
        private readonly RanksRepository $ranksRepository,
        private readonly ProtocolLineService $protocolLineService,
        private readonly PersonsService $personsService,
        private readonly ViewPersonService $viewPersonService,
    ) {
    }

    public function getPersonRanks(int $personId): Collection
    {
        $filter = new RanksFilter();
        $filter->personId = $personId;
        $filter->isOrderDescByFinishDate = true;

        return $this->ranksRepository->getRanksList($filter);
    }

    public function activateRank(Rank $rank, Carbon $startDate): void
    {
        $protocolLineId = $this->protocolLineService->getProtocolLineIdForRank($rank);
        $protocolLine = $this->protocolLineService->getProtocolLineWithEvent($protocolLineId);

        if (!$protocolLine) {
            return;
        }

        $protocolLine->activate_rank = $startDate;
        $protocolLine->save();

        $this->reFillRanksForPerson($rank->person_id);
    }

    public function reFillRanksForPerson(int $personId): void
    {
        try {
            $this->viewPersonService->execute(new ViewPerson((string)$personId));
        } catch (PersonNotFound) {
            return;
        }

        $ranks = $this->getPersonRanks($personId);
        $this->ranksRepository->deleteRanks($ranks);

        foreach ($this->protocolLineService->getPersonProtocolLines($personId) as $protocolLine) {
            $this->fillRank($protocolLine);
        }
    }

    public function getFinishedRanks(string $rank): Collection
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
        $ranks = Collection::empty();

        foreach ($personsIds as $personId) {
            $actualRank = $this->getActiveRank($personId, $nowDate);
            if ($actualRank && $actualRank->rank === $rank) {
                $ranks->put($personId, $actualRank);
            }
        }

        return $ranks;
    }

    public function getActiveRank(int $personId, Carbon $date = null): ?Rank
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
            $rank = $this->getActiveRank($personId);
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

        if (!$this->checkMaxJuniorAge($protocolLine->person_id, $protocolLine->complete_rank)) {
            return;
        }

        $event = $protocolLine->event;
        $actualRank = $this->getActiveRank($protocolLine->person_id, $protocolLine->event->date);

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

                if ($protocolLine->activate_rank) {
                    $ranks->each(function (Rank $rank) use ($finishDate): void {
                        $rank->finish_date = $finishDate;
                        $this->ranksRepository->storeRank($rank);
                    });
                }

                // Надо взять все разряды которые после этой даты
                // Отсортировать их по дате евента и заново пересохранить
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = $actualRank->person_id;
                $ranksFilter->startDateMore = $event->date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);
                $protocolLines = new Collection();
                $ranks->each(function (Rank $rank) use (&$protocolLines): void {
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
                foreach ($protocolLines as $line) {
                    /** @var ProtocolLine $line */
                    $this->fillRank($line);
                }
            }
        } else {
            $newRank = $this->createNewRank($protocolLine);
            $this->ranksRepository->storeRank($newRank);
        }
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

    //еслі разряд просрочілся то создаётся новый с более низким разрядом
    private function createPreviousRank(Rank $rank, Carbon $date = null): ?Rank
    {
        if ($date === null) {
            $date = new Carbon('now');
        }
        if ($rank->finish_date < $date) {
            if (!isset(Rank::PREVIOUS_RANKS[$rank->rank])) {
                return null;
            }

            $newRank = $rank->replicate();
            $newRank->start_date = $newRank->finish_date->addDay();
            $newRank->finish_date = $newRank->start_date->addYears(2);
            $newRank->activated_date = $newRank->start_date;
            $newRank->event_id = null;
            $newRank->rank = Rank::PREVIOUS_RANKS[$rank->rank];

            if (!$this->checkMaxJuniorAge($newRank->person_id, $newRank->rank)) {
                return null;
            }

            $newRank = $this->ranksRepository->storeRank($newRank);

            return $this->createPreviousRank($newRank, $date);
        }

        return $rank;
    }

    /**
     * подходит ли этот юношеский разряд под возраст, или это вообще не юношеский разряд
     */
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
        $lastRank->start_date = $protocolLine->activate_rank ?: $protocolLine->event->date;
        $lastRank->finish_date = $lastRank->start_date->clone()->addYears(2);
        $lastRank->activated_date = $protocolLine->activate_rank;

        return $lastRank;
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
            $results = $results->filter(static fn (ProtocolLine $line) => $line->time !== null && !$line->vk);
            if ($results->count() >= 3) {
                $results = $results
                    ->sortBy(static fn (ProtocolLine $line) => $line->event->date)
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
