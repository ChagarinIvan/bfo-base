<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Domain\Cup\CupEvent\CupEvent;

final readonly class CupEventAssembler
{
    public function __construct(
        private AuthAssembler $authAssembler,
        // TODO remove
        private EventAssembler $eventAssembler,
    ) {
    }

    public function toViewCupEventDto(CupEvent $cupEvent): ViewCupEventDto
    {
        return new ViewCupEventDto(
            id: (string) $cupEvent->id,
            cupId: (string) $cupEvent->cup_id,
            eventId: (string) $cupEvent->event_id,
            points: (string) $cupEvent->points,
            created: $this->authAssembler->toImpressionDto($cupEvent->created),
            updated: $this->authAssembler->toImpressionDto($cupEvent->updated),

            // TODO remove
            event: $this->eventAssembler->toViewEventDto($cupEvent->event),
        );
    }
}
