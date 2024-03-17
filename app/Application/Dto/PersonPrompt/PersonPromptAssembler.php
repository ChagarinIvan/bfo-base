<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPrompt;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\PersonPrompt\PersonPrompt;

final readonly class PersonPromptAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewPersonPromptDto(PersonPrompt $personPrompt): ViewPersonPromptDto
    {
        return new ViewPersonPromptDto(
            id: (string) $personPrompt->id,
            personId: (string) $personPrompt->person_id,
            prompt: $personPrompt->prompt,
            metaphone: $personPrompt->metaphone,
            created: $this->authAssembler->toImpressionDto($personPrompt->created),
            updated: $this->authAssembler->toImpressionDto($personPrompt->updated),
        );
    }
}
