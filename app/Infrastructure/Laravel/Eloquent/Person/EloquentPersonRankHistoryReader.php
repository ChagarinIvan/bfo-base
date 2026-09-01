<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Application\Dto\Person\PersonRankHistoryDto;
use App\Application\Port\PersonRankHistoryReader;

final readonly class EloquentPersonRankHistoryReader implements PersonRankHistoryReader
{
    public function byId(int $historyId): ?PersonRankHistoryDto
    {
        $history = PersonRankHistoryRecord::query()
            ->with('protocolLine.distance.event.competition')
            ->find($historyId);

        return $history === null ? null : $this->toDto($history);
    }

    public function forPerson(int $personId): array
    {
        return PersonRankHistoryRecord::query()
            ->where('person_id', $personId)
            ->with('protocolLine.distance.event.competition')
            ->orderBy('achieved_on')
            ->orderBy('id')
            ->get()
            ->map($this->toDto(...))
            ->all();
    }

    private function toDto(PersonRankHistoryRecord $history): PersonRankHistoryDto
    {
        $event = $history->protocolLine?->distance?->event;

        return new PersonRankHistoryDto(
            id: (string) $history->id,
            protocolLineId: (string) $history->protocol_line_id,
            distanceId: (string) $history->distance_id,
            eventId: (string) $history->event_id,
            competitionId: (string) $history->competition_id,
            rankId: $history->rank->value,
            changeType: $history->change_type,
            achievedOn: $history->achieved_on->format('Y-m-d'),
            activatedOn: $history->activated_on?->format('Y-m-d'),
            startedOn: $history->started_on->format('Y-m-d'),
            finishedOn: $history->finished_on?->format('Y-m-d'),
            rank: $history->rank->label(),
            eventDate: $event?->date?->format('Y-m-d'),
            competitionName: $event?->competition?->name,
            eventName: $event?->name,
        );
    }
}
