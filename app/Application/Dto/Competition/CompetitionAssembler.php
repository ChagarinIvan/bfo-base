<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Models\Competition;

final readonly class CompetitionAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewCompetitionDto(Competition $competition): ViewCompetitionDto
    {
        return new ViewCompetitionDto(
            id: (string) $competition->id,
            name: $competition->name,
            description: $competition->description,
            from: $competition->from->format('Y-m-d'),
            to: $competition->to->format('Y-m-d'),
            created: $this->authAssembler->toImpressionDto($competition->created),
            updated: $this->authAssembler->toImpressionDto($competition->updated)
        );
    }
}
