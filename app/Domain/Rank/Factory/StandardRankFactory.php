<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Clock;

final readonly class StandardRankFactory implements RankFactory
{
    public function create(RankInput $input): Rank
    {
        $rank = new Rank();
        $rank->person_id = $input->personId;
        $rank->event_id = $input->eventId;
        $rank->rank = $input->rank;
        $rank->start_date = $input->startDate;
        $rank->finish_date = $input->finishDate ?: $input->startDate->clone()->addYears(2);
        $rank->activated_date = $input->activatedDate;

        return $rank;
    }
}
