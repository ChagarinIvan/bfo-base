<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

interface PersonPromptMetaphone
{
    public function calculate(string $prompt): string;
}
