<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Event\Event;
use App\Domain\Event\EventResources;

final readonly class EventAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
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
            competitionName: $resources->withCompetitionName && $event->relationLoaded('competition')
                ? $event->competition?->name
                : null,
        );
    }
}
