<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Rank\RankRepository as RanksRepositoryInterface;
use App\Filters\RanksFilter;
use App\Repositories\RanksRepository;
use Carbon\Carbon;

final readonly class PreviousRanksFinishDateUpdater
{
    public function __construct(
        private RanksRepository $ranksRepository,
        private RanksRepositoryInterface $ranks,
    ) {
    }

    public function update(int $personId, string $rank, Carbon $startDate, Carbon $finishDate): void
    {
        $ranksFilter = new RanksFilter();
        $ranksFilter->personId = $personId;
        $ranksFilter->rank = $rank;
        $ranksFilter->startDateLess = $startDate;
        $ranksFilter->finishDateMore = $startDate;

        $ranks = $this->ranksRepository->getRanksList($ranksFilter);

        $ranks->each(function (Rank $rank) use ($finishDate): void {
            $rank->finish_date = $finishDate->clone();
            $rank->setAttribute('finish_date', $finishDate->clone());
            $this->ranks->add($rank);
        });
    }
}
