<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

interface PromptIdentifier
{
    public function identPerson(string $searchLine): ?int;
}
