<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

final readonly class UpdatePersonPromptInput
{
    public function __construct(
        public string $prompt,
        public int $userId,
    ) {
    }
}
