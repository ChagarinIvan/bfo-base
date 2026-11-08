<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use DateTimeImmutable;

final readonly class PersonAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewPersonDto(Person $person): ViewPersonDto
    {
        return new ViewPersonDto(
            id: (string) $person->id,
            lastname: $person->lastname,
            firstname: $person->firstname,
            birthday: $person->birthday?->format('Y-m-d'),
            rankId: $person->currentRank()->rank->value,
            rankCalculatedAt: $person->rank_calculated_at?->format(DateTimeImmutable::ATOM),
            rankCalculationReason: $person->rank_calculation_reason->value,
            citizenship: $person->citizenship->value,
            clubId: $person->club_id ? (string) $person->club_id : null,
            created: $this->authAssembler->toImpressionDto($person->created),
            updated: $this->authAssembler->toImpressionDto($person->updated),
        );
    }

    public function toViewPersonRankHistoryDto(PersonRankHistory $history): ViewPersonRankHistoryDto
    {
        return new ViewPersonRankHistoryDto(
            id: (string) $history->id,
            personId: (string) $history->person_id,
            protocolLineId: $history->protocol_line_id === null ? null : (string) $history->protocol_line_id,
            distanceId: $history->distance_id === null ? null : (string) $history->distance_id,
            eventId: $history->event_id === null ? null : (string) $history->event_id,
            competitionId: $history->competition_id === null ? null : (string) $history->competition_id,
            rankId: $history->rank->value,
            changeType: $history->change_type->value,
            achievedOn: $history->achieved_on->format('Y-m-d'),
            activatedOn: $history->activated_on?->format('Y-m-d'),
            startedOn: $history->started_on->format('Y-m-d'),
            finishedOn: $history->finished_on?->format('Y-m-d'),
            source: $history->manual_rank_id === null ? 'protocol' : 'manual',
            manualRankId: $history->manual_rank_id,
        );
    }
}
