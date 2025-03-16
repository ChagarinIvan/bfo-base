<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Filters\RanksFilter;
use App\Models\Year;
use App\Repositories\RanksRepository;
use Carbon\Carbon;

final readonly class PreviousRanksFinishDateUpdater
{
    public function __construct(private RanksRepository $ranksRepository)
    {
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
            $this->ranksRepository->storeRank($rank);
        });
    }
}
