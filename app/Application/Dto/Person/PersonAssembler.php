<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Person;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use function array_map;

final readonly class PersonAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewPersonDto(Person $person, bool $withProtocolLines = false): ViewPersonDto
    {
        if ($withProtocolLines) {
            $groupedProtocolLines = $person->protocolLines->groupBy(static fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
            $groupedProtocolLines->transform(static function (Collection $protocolLines) {
                return $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->distance->event->date);
            });
            $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();
        }

        return new ViewPersonDto(
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
            groupedByYearProtocolLines: $withProtocolLines
                ? array_map(fn (Collection $c) => $c->map($this->toViewPersonProtocolLineDto(...))->all(), $groupedProtocolLines->all())
                : [],
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
}
