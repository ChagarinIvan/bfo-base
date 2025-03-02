<?php

declare(strict_types=1);

namespace App\Services;

use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ViewPerson;
use App\Application\Service\Person\ViewPersonService;
use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Rank\JuniorRankAgeValidator;
use App\Domain\Rank\Rank;
use App\Filters\RanksFilter;
use App\Models\Year;
use App\Repositories\RanksRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
        private readonly ViewPersonService $viewPersonService,
        private readonly JuniorRankAgeValidator $juniorRankAgeChecker,
        private readonly ActivePersonRankService $activePersonRankService,
        private readonly RankFactory $factory,
    ) {
    }

    public function getPersonRanks(int $personId): Collection
    {
        $filter = new RanksFilter();
        $filter->personId = $personId;
        $filter->isOrderDescByFinishDate = true;

        return $this->ranksRepository->getRanksList($filter);
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
            $actualRank = $this->activePersonRankService->execute(new ActivePersonRank((string)$personId));
            if ($actualRank && $actualRank->rank === $rank) {
                $ranks->put($personId, $actualRank);
            }
        }

        return $ranks;
    }

    public function getActualRanks(Collection $personIds): Collection
    {
        $actualRanks = new Collection();
        foreach ($personIds as $personId) {
            $rank = $this->activePersonRankService->execute(new ActivePersonRank((string)$personId));
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
        if (
            $protocolLine->complete_rank === null
            || empty(trim($protocolLine->complete_rank))
            || !isset(self::RANKS_POWER[$protocolLine->complete_rank])
            || !Rank::validateRank($protocolLine->complete_rank)
        ) {
            return;
        }

        if (!$this->juniorRankAgeChecker->validate($protocolLine->person_id, $protocolLine->complete_rank, Year::actualYear())) {
            return;
        }

        dump('Search acutal rankt for date ' . $protocolLine->event->date->format('Y-m-d'));
        $event = $protocolLine->event;
        $actualRankDto = $this->activePersonRankService->execute(new ActivePersonRank((string)$protocolLine->person_id, $protocolLine->event->date));

        dump('Actual rank ' . $actualRankDto?->rank ?? '---');
        if ($actualRankDto) {
            if ($actualRankDto->rank === $protocolLine->complete_rank) {
                $newRank = $this->factory->create(new RankInput(
                    personId: (int) $actualRankDto->personId,
                    eventId: $event->id,
                    rank: $actualRankDto->rank,
                    startDate: Carbon::createFromFormat('Y-m-d', $actualRankDto->startDate),
                    activatedDate: $protocolLine->activate_rank,
                ));

                if ($event->date > Carbon::createFromFormat('Y-m-d', ($actualRankDto->eventId === null ? $actualRankDto->startDate : $actualRankDto->eventDate))) {
                    $newRank->finish_date = $event->date->clone()->addYears(2);
                }
                dump('prolongate rank ' . $actualRankDto->rank);
                $this->ranksRepository->storeRank($newRank);

                // трэба абнавіць усе папярэднія разряды
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = (int) $actualRankDto->personId;
                $ranksFilter->rank = $newRank->rank;
                $ranksFilter->startDateLess = $newRank->start_date;
                $ranksFilter->finishDateMore = $newRank->finish_date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);

                dump('Update previous ranks ', count($ranks));
                $ranks->each(function (Rank $rank) use ($newRank): void {
                    dump($newRank->finish_date->format('Y-m-d'));
                    $rank->finish_date = $newRank->finish_date->clone();
                    dump($rank->finish_date->format('Y-m-d'));
                    $this->ranksRepository->storeRank($rank);
                });
            } elseif (!empty(trim($actualRankDto->rank)) && (self::RANKS_POWER[$protocolLine->complete_rank] > self::RANKS_POWER[$actualRankDto->rank])) {
                // трэба зачыніць усе папярэднія разряды
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = (int) $actualRankDto->personId;
                $ranksFilter->rank = $actualRankDto->rank;
                $ranksFilter->startDateLess = $event->date;
                $ranksFilter->finishDateMore = $event->date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);
                if ($event->date->format('Y-m-d') !== $protocolLine->activate_rank?->format('Y-m-d')) {
                    $finishDate = $protocolLine->activate_rank;
                } else {
                    $finishDate = $event->date->clone()->addDays(-1);
                }

                if ($protocolLine->activate_rank) {
                    $ranks->each(function (Rank $rank) use ($finishDate): void {
                        $rank->finish_date = $finishDate;
                        $this->ranksRepository->storeRank($rank);
                    });
                }

                // Надо взять все разряды которые после этой даты
                // Отсортировать их по дате евента и заново пересохранить
                $ranksFilter = new RanksFilter();
                $ranksFilter->personId = (int) $actualRankDto->personId;
                $ranksFilter->startDateMore = $event->date;
                $ranks = $this->ranksRepository->getRanksList($ranksFilter);

                $protocolLines = new Collection();

                $ranks->each(function (Rank $rank) use (&$protocolLines): void {
                    $protocolLineId = $this->protocolLineService->getProtocolLineIdForRank($rank);
                    $pl = $this->protocolLineService->getProtocolLineWithEvent($protocolLineId);
                    if ($pl) {
                        $protocolLines->push($pl);
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
            dump('Create new rank');
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
        dump('storeRank in RanksService ' . $this->rank);
        $rank->save();
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
}
