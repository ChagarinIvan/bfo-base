<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt\Factory;

use App\Domain\PersonPrompt\PersonPrompt;

interface PersonPromptFactory
{
    public function create(PersonPromptInput $input): PersonPrompt;
}
