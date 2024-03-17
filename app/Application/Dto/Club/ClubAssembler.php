<?php

declare(strict_types=1);

namespace App\Application\Dto\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Club\Club;

final readonly class ClubAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewClubDto(Club $club): ViewClubDto
    {
        return new ViewClubDto(
            id: (string) $club->id,
            name: $club->name,
            personsCount: $club->persons->count(),
            created: $this->authAssembler->toImpressionDto($club->created),
            updated: $this->authAssembler->toImpressionDto($club->updated)
        );
    }
}
