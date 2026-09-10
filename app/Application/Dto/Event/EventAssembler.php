<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Event\Event;
use App\Domain\Event\EventResources;
use App\Domain\Event\Protocol;

final readonly class EventAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toLegacyViewEventDto(Event $event): LegacyViewEventDto
    {
        return new LegacyViewEventDto(
            id: (string) $event->id,
            competitionId: (string) $event->competition_id,
            name: $event->name,
            description: $event->description,
            date: $event->date->format('Y-m-d'),
            competitionName: $event->competition?->name ?: '',
            protocolLinesCount: $event->protocolLines->count(),
            firstDistance: $event->distances->first(),
            cups: $event->cups->all(),
            distances: $event->distances->all(),
            created: $this->authAssembler->toImpressionDto($event->created),
            updated: $this->authAssembler->toImpressionDto($event->updated)
        );
    }

    public function toViewEventDto(
        Event $event,
        EventResources $resources = new EventResources(),
    ): ViewEventDto {
        return new ViewEventDto(
            id: (string) $event->id,
            competitionId: (string) $event->competition_id,
            name: $event->name,
            description: $event->description,
            date: $event->date->format('Y-m-d'),
            created: $this->authAssembler->toImpressionDto($event->created),
            updated: $this->authAssembler->toImpressionDto($event->updated),
            participantsCount: (int) $event->getAttribute('protocol_lines_count'),
            competitionName: $resources->competitionName && $event->relationLoaded('competition')
                ? $event->competition?->name
                : null,
            cups: $resources->withCups
                ? $event->cups->map($this->toViewEventCupDto(...))->all()
                : null,
        );
    }

    public function toViewEventCupDto(CupEvent $cupEvent): ViewEventCupDto
    {
        return new ViewEventCupDto(
            id: (string) $cupEvent->cup_id,
            name: $cupEvent->cup->name,
            year: $cupEvent->cup->year->value,
        );
    }

    public function toViewEventProtocolDto(Event $event, Protocol $eventProtocol): ViewEventProtocolDto
    {
        return new ViewEventProtocolDto(
            name: $event->date . '_' . $event->name,
            content: $eventProtocol->content,
            extension: $eventProtocol->extension,
        );
    }
}
