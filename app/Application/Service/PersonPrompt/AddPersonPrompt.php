<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Domain\PersonPrompt\Factory\PersonPromptInput;

final readonly class AddPersonPrompt
{
    public function __construct(
        private PersonPromptDto $prompt,
        private string $personId,
        private UserId $userId,
    ) {
    }

    public function input(): PersonPromptInput
    {
        return new PersonPromptInput(
            prompt: $this->prompt->prompt,
            personId: (int) $this->personId,
            userId: $this->userId->id
        );
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }
}
