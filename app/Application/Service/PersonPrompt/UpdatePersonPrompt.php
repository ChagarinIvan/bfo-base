<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Domain\Competition\CompetitionInfo;
use App\Domain\Competition\Factory\CompetitionInput;
use App\Domain\PersonPrompt\Factory\PersonPromptInput;
use App\Domain\PersonPrompt\UpdatePersonPromptInput;
use Carbon\Carbon;

final readonly class UpdatePersonPrompt
{
    public function __construct(
        private PersonPromptDto $prompt,
        private string $promptId,
        private UserId $userId,
    ) {
    }

    public function id(): int
    {
        return (int) $this->promptId;
    }

    public function input(): UpdatePersonPromptInput
    {
        return new UpdatePersonPromptInput(
            prompt: $this->prompt->prompt,
            userId: $this->userId->id
        );
    }
}
