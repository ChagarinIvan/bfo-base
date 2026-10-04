<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Shared\Criteria;
use function abs;
use function array_key_exists;
use function array_unique;
use function levenshtein;
use function strlen;
use function substr;

final class StandardPromptIdentifier implements PromptIdentifier
{
    private const int MAX_METAPHONE_DISTANCE = 2;
    private const int MAX_PROMPT_DISTANCE = 5;

    /** @var array<string, list<array{prompt: string, personId: int}>>|null */
    private ?array $promptsByMetaphone = null;

    /** @var array<string, string|null> */
    private array $nearestMetaphones = [];

    public function __construct(
        private readonly PersonPromptRepository $personPrompts,
        private readonly PersonPromptMetaphone $metaphone,
    ) {
    }

    public function identPerson(string $searchLine): ?int
    {
        $index = $this->prompts();
        if ($index === []) {
            return null;
        }

        $search = $this->metaphone->calculate($searchLine);
        $key = '#' . $search;
        if (!array_key_exists($key, $this->nearestMetaphones)) {
            $this->nearestMetaphones[$key] = $this->nearestMetaphone($search, $index);
        }
        $metaphone = $this->nearestMetaphones[$key];

        if ($metaphone === null) {
            return null;
        }

        $personId = null;
        $minimumDistance = self::MAX_PROMPT_DISTANCE + 1;
        foreach ($index['#' . $metaphone] as $prompt) {
            if (abs(strlen($searchLine) - strlen($prompt['prompt'])) > self::MAX_PROMPT_DISTANCE) {
                continue;
            }

            $distance = levenshtein($searchLine, $prompt['prompt']);
            if ($distance < $minimumDistance) {
                $minimumDistance = $distance;
                $personId = $prompt['personId'];
                if ($distance === 0) {
                    break;
                }
            }
        }

        return $personId;
    }

    public function match(array $preparedLines): array
    {
        $matched = [];
        foreach (array_unique($preparedLines) as $line) {
            $personId = $this->identPerson($line);
            if ($personId !== null && $personId > 0) {
                $matched[$line] = $personId;
            }
        }

        return $matched;
    }

    /** @return array<string, list<array{prompt: string, personId: int}>> */
    private function prompts(): array
    {
        if ($this->promptsByMetaphone !== null) {
            return $this->promptsByMetaphone;
        }

        $index = [];
        foreach ($this->personPrompts->byCriteria(Criteria::empty()) as $prompt) {
            $index['#' . $prompt->metaphone][] = [
                'prompt' => $prompt->prompt,
                'personId' => $prompt->person_id,
            ];
        }

        return $this->promptsByMetaphone = $index;
    }

    /** @param array<string, list<array{prompt: string, personId: int}>> $index */
    private function nearestMetaphone(string $search, array $index): ?string
    {
        if (isset($index['#' . $search])) {
            return $search;
        }

        $nearest = null;
        $minimumDistance = self::MAX_METAPHONE_DISTANCE + 1;
        foreach ($index as $metaphone => $prompts) {
            $metaphone = substr($metaphone, 1);
            if (abs(strlen($search) - strlen($metaphone)) > self::MAX_METAPHONE_DISTANCE) {
                continue;
            }

            $distance = levenshtein($search, $metaphone);
            if ($distance < $minimumDistance) {
                $minimumDistance = $distance;
                $nearest = $metaphone;
            }
        }

        return $nearest;
    }
}
