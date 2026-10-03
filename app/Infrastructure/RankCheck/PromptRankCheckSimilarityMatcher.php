<?php

declare(strict_types=1);

namespace App\Infrastructure\RankCheck;

use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\RankCheck\RankCheckSimilarityMatcher;
use App\Domain\Shared\Criteria;
use function array_unique;
use function levenshtein;

final readonly class PromptRankCheckSimilarityMatcher implements RankCheckSimilarityMatcher
{
    public function __construct(
        private PersonPromptRepository $personPrompts,
        private PersonPromptMetaphone $metaphone,
    ) {
    }

    public function match(array $preparedLines): array
    {
        if ($preparedLines === []) {
            return [];
        }

        $groups = [];
        foreach ($this->personPrompts->byCriteria(Criteria::empty()) as $prompt) {
            $key = '#' . $prompt->metaphone;
            $groups[$key]['metaphone'] = $prompt->metaphone;
            $groups[$key]['prompts'][] = ['prompt' => $prompt->prompt, 'personId' => $prompt->person_id];
        }

        if ($groups === []) {
            return [];
        }

        $candidates = [];
        $matched = [];
        foreach (array_unique($preparedLines) as $line) {
            $metaphone = $this->metaphone->calculate($line);
            $key = '#' . $metaphone;
            $prompts = $candidates[$key] ??= $this->closestPrompts($metaphone, $groups);
            $bestDistance = 6;
            $personId = 0;
            foreach ($prompts as $prompt) {
                $distance = levenshtein($line, $prompt['prompt']);
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $personId = $prompt['personId'];
                }
                if ($distance === 0) {
                    break;
                }
            }

            if ($personId > 0) {
                $matched[$line] = $personId;
            }
        }

        return $matched;
    }

    /**
     * @param array<string, array{metaphone: string, prompts: list<array{prompt: string, personId: int}>}> $groups
     * @return list<array{prompt: string, personId: int}>
     */
    private function closestPrompts(string $metaphone, array $groups): array
    {
        $bestDistance = 3;
        $prompts = [];
        foreach ($groups as $group) {
            $distance = levenshtein($metaphone, $group['metaphone']);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $prompts = $group['prompts'];
            }
            if ($distance === 0) {
                break;
            }
        }

        return $prompts;
    }
}
