<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\CupRepository;
use App\Domain\CupEvent\CupEvent;

final readonly class CupEventAssembler
{
    public function __construct(
        private EventAssembler $eventAssembler,
        private AuthAssembler $authAssembler,
    ) {
    }

    public function toViewCupEventDto(CupEvent $cupEvent, CupRepository $cups): ViewCupEventDto
    {
        $cup = $cups->byId($cupEvent->cup_id) ?? throw new CupNotFound;

        return new ViewCupEventDto(
            id: (string) $cupEvent->id,
            cupId: (string) $cupEvent->cup_id,
            eventId: (string) $cupEvent->event_id,
            points: (string) $cupEvent->points,
            participatesCount: $cup->type->instance()->getCupEventParticipatesCount($cupEvent),
            created: $this->authAssembler->toImpressionDto($cupEvent->created),
            updated: $this->authAssembler->toImpressionDto($cupEvent->updated),

            // TODO remove
            event: $this->eventAssembler->toViewEventDto($cupEvent->event),
        );
    }
}
