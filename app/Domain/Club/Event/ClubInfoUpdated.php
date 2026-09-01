<?php

declare(strict_types=1);

namespace App\Domain\Club\Event;

use App\Domain\Club\Club;
use App\Domain\Shared\AggregatedEvent;

final readonly class ClubInfoUpdated extends AggregatedEvent
{
    public function __construct(public Club $club)
    {
    }
}
