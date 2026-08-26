<?php

declare(strict_types=1);

namespace App\Application\Service\Rank\Criteria;

use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;

final readonly class RankProtocolLineCriteria
{
    public static function create(Rank $rank): Criteria
    {
        return new Criteria([
            'personId' => $rank->person_id,
            'eventId' => $rank->event_id,
        ]);
    }
}
