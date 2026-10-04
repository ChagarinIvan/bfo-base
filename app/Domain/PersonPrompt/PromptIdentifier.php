<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

interface PromptIdentifier
{
    /**
     * @param list<string> $preparedLines
     * @return array<string, int>
     */
    public function match(array $preparedLines): array;

    public function identPerson(string $searchLine): ?int;
}
