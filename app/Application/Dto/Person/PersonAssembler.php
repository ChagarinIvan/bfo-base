<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Models\Person;

final readonly class PersonAssembler
{
    public function toViewPersonDto(Person $person): ViewPersonDto
    {
        return new ViewPersonDto(
            id: $person->id,
            lastname: $person->lastname,
            firstname: $person->firstname,
            birthday: $person->birthday->format('Y-m-d'),
            clubId: $person->club_id,
            eventsCount: $person->protocol_lines_count ?? 0,
        );
    }
}
