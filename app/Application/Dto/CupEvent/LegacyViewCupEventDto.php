<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\Event\ViewEventDto;

final readonly class LegacyViewCupEventDto
{
    public function __construct(
        public string $id,
        public string $cupId,
        public string $eventId,
        public string $points,
        public ViewEventDto $event,
    ) {
    }
}
