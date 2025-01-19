<?php

declare(strict_types=1);

namespace App\Domain\Rank\Event;

use App\Domain\Rank\Rank;
use App\Domain\Shared\AggregatedEvent;

final readonly class RankCreated extends AggregatedEvent
{
    public function __construct(public Rank $rank)
    {
    }
}
