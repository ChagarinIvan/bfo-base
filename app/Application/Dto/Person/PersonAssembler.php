<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\Person;

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
            clubId: $person->club_id ? (string) $person->club_id : null,
            eventsCount: $person->protocol_lines_count ?? 0,
            created: $this->authAssembler->toImpressionDto($person->created),
            updated: $this->authAssembler->toImpressionDto($person->updated)
        );
    }
}
