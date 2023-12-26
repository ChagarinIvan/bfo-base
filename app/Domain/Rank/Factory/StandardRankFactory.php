<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankId;
use App\Domain\Shared\Clock;

final readonly class StandardRankFactory implements RankFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(RankInput $input): Rank
    {
        $impression = new Impression($this->clock->now(), $input->by);

        return new Rank(
            id: RankId::random(),
            personId: $input->personId,
            eventId: $input->eventId,
            type: $input->type,
            completedAt: $input->completedAt,
            impression: $impression,
        );
    }
}
