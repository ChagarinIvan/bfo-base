<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class EventInput
{
    public function __construct(
        public EventInfo $info,
        public int $competitionId,
        public int $userId,
    ) {
    }
}
