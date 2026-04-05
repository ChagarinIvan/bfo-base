<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt\Factory;

final readonly class PersonPromptInput
{
    public function __construct(
        public string $prompt,
        public int $personId,
        public int $userId,
    ) {
    }
}
