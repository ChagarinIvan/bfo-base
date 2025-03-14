<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Event\Event;
use App\Domain\Event\Protocol;

final readonly class EventAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewEventDto(Event $event): ViewEventDto
    {
        return new ViewEventDto(
            id: (string) $event->id,
            competitionId: (string) $event->competition_id,
            name: $event->name,
            description: $event->description,
            date: $event->date->format('Y-m-d'),
            competitionName: $event->competition?->name ?: '',
            protocolLinesCount: $event->protocolLines->count(),
            firstDistance: $event->distances->first(),
            cups: $event->cups->all(),
            flags: $event->flags->all(),
            created: $this->authAssembler->toImpressionDto($event->created),
            updated: $this->authAssembler->toImpressionDto($event->updated)
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
