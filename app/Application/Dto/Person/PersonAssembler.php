<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Person;
use App\Domain\Person\PersonResources;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastructure\Laravel\Eloquent\Person\PersonRankHistoryRecord;
use Illuminate\Support\Collection;
use function array_map;

final readonly class PersonAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toLegacyViewPersonDto(Person $person, PersonResources $resources = new PersonResources()): LegacyViewPersonDto
    {
        $currentRank = $person->currentRank();

        if ($resources->protocolLines) {
            $groupedProtocolLines = $person->protocolLines->groupBy(static fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
            $groupedProtocolLines->transform(static fn(Collection $protocolLines) => $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->distance->event->date));
            $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();
        }

        return new LegacyViewPersonDto(
            id: (string) $person->id,
            lastname: $person->lastname,
            firstname: $person->firstname,
            birthday: $person->birthday?->format('Y-m-d'),
            citizenship: $person->citizenship->value,
            clubId: $person->club_id ? (string) $person->club_id : null,
            eventsCount: $person->protocol_lines_count ?? 0,
            created: $this->authAssembler->toImpressionDto($person->created),
            updated: $this->authAssembler->toImpressionDto($person->updated),
            // TODO remove
            lastPaymentDate: $person->payments->sortByDesc(static fn (PersonPayment $payment) => $payment->date)->first()?->date?->format('Y-m-d'),
            groupedByYearProtocolLines: $resources->protocolLines
                ? array_map(fn (Collection $c) => $c->map($this->toViewPersonProtocolLineDto(...))->all(), $groupedProtocolLines->all())
                : [],
            currentRankId: $currentRank->rank->value,
            currentRankFinishedOn: $currentRank->finishedOn?->format('Y-m-d'),
            rankHistory: $resources->rankHistory ? $person->rankHistoryRecords->map($this->toPersonRankHistoryDto(...))->all() : [],
        );
    }

    public function toViewPersonDto(Person $person): ViewPersonDto
    {
        $currentRank = $person->currentRank();

        return new ViewPersonDto(
            id: (string) $person->id,
            lastname: $person->lastname,
            firstname: $person->firstname,
            birthYear: $person->birthday?->year,
            rankId: $currentRank->rank->value,
            created: $this->authAssembler->toImpressionDto($person->created),
            updated: $this->authAssembler->toImpressionDto($person->updated),
        );
    }

    public function toViewPersonProtocolLineDto(ProtocolLine $line): ViewPersonProtocolLineDto
    {
        return new ViewPersonProtocolLineDto(
            id: (string) $line->id,
            firstname: $line->firstname,
            lastname: $line->lastname,
            distanceId: (string) $line->distance_id,
            competitionId: (string) $line->distance->event->competition_id,
            competitionName: $line->distance->event->competition->name,
            eventName: $line->distance->event->name,
            eventDate: $line->distance->event->date->format('Y-m-d'),
            groupName: $line->distance->group->name,
            year: $line->year ? (string) $line->year : null,
            time: $line->time?->format('H:i:s'),
            place: $line->place ? (string) $line->place : null,
            completeRank: $line->complete_rank,
        );
    }

    private function toPersonRankHistoryDto(PersonRankHistoryRecord $history): PersonRankHistoryDto
    {
        $event = $history->protocolLine?->distance?->event;

        return new PersonRankHistoryDto(
            id: (string) $history->id,
            personId: (string) $history->person_id,
            protocolLineId: (string) $history->protocol_line_id,
            distanceId: (string) $history->distance_id,
            eventId: (string) $history->event_id,
            competitionId: (string) $history->competition_id,
            rankId: $history->rank->value,
            changeType: $history->change_type->value,
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
