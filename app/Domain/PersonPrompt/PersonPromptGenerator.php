<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

interface PersonPromptGenerator
{
    /** @return list<string> */
    public function generate(string $firstname, string $lastname, ?string $birthdayYear = null, bool $hasNamesake = false): array;
}
