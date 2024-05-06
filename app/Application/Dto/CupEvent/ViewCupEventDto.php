<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\Event\ViewEventDto;

final readonly class ViewCupEventDto
{
    public function __construct(
        public string $id,
        public string $cupId,
        public string $eventId,
        public string $points,

        // TODO remove
        public ViewEventDto $event,
    ) {
    }
}
