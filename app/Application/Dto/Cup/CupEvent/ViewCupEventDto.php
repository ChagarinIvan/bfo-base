<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup\CupEvent;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Event\ViewEventDto;

final readonly class ViewCupEventDto
{
    public function __construct(
        public string $id,
        public string $cupId,
        public string $eventId,
        public string $points,
        public int $participatesCount,
        public ImpressionDto $created,
        public ImpressionDto $updated,

        // TODO remove
        public ViewEventDto $event,
    ) {
    }
}
