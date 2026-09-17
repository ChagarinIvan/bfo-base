<?php

declare(strict_types=1);

namespace App\Domain\RankCheck\Event;

use App\Domain\RankCheck\RankCheck;
use App\Domain\Shared\AggregatedEvent;

final readonly class RankCheckCreated extends AggregatedEvent
{
    public function __construct(public RankCheck $rankCheck)
    {
    }
}
