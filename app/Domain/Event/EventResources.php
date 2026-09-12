<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class EventResources
{
    public function __construct(
        public bool $withCompetitionName = false,
        public bool $withDistances = false,
        public bool $withProtocolLines = false,
    )
    {
    }
}
