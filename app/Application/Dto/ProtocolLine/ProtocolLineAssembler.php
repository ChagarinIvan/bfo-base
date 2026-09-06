<?php

declare(strict_types=1);

namespace App\Application\Dto\ProtocolLine;

use App\Domain\ProtocolLine\ProtocolLine;

final readonly class ProtocolLineAssembler
{
    public function toViewProtocolLineDto(ProtocolLine $line): ViewProtocolLineDto
    {
        $distance = $line->relationLoaded('distance') ? $line->distance : null;
        $event = $distance?->relationLoaded('event') ? $distance->event : null;
        $competition = $event?->relationLoaded('competition') ? $event->competition : null;
        $group = $distance?->relationLoaded('group') ? $distance->group : null;

        return new ViewProtocolLineDto(
            id: (string) $line->id,
            personId: (string) $line->person_id,
            firstname: $line->firstname,
            lastname: $line->lastname,
            distanceId: (string) $line->distance_id,
            eventId: $distance === null ? null : (string) $distance->event_id,
            competitionId: $event === null ? null : (string) $event->competition_id,
            competitionName: $competition?->name,
            eventName: $event?->name,
            eventDate: $event?->date?->format('Y-m-d'),
            groupName: $group?->name,
            year: $line->year === null ? null : (string) $line->year,
            time: $line->time?->format('H:i:s'),
            place: $line->place === null ? null : (string) $line->place,
            completeRank: $line->complete_rank ?: null,
        );
    }
}
