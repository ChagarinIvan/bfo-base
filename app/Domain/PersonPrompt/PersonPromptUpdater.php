<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

interface PersonPromptUpdater
{
    public function update(PersonPrompt $prompt, UpdatePersonPromptInput $input): PersonPrompt;
}
