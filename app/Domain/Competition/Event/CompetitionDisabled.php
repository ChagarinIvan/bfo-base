<?php

declare(strict_types=1);

namespace App\Domain\Competition\Event;

use App\Domain\Competition\Competition;
use App\Domain\Shared\AggregatedEvent;

final readonly class CompetitionDisabled extends AggregatedEvent
{
    public function __construct(public Competition $competition)
    {
    }
}
