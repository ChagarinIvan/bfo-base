<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface PersonPromptUpdater
{
    public function update(PersonPrompt $prompt, UpdatePersonPromptInput $input): PersonPrompt;
}
