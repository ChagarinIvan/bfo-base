<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use function array_unique;

final readonly class StandardRankCheckPersonMatcher implements RankCheckPersonMatcher
{
    public function __construct(
        private PersonPromptRepository $personPrompts,
        private RankCheckSimilarityMatcher $identification,
    ) {
    }

    public function match(array $preparedLines): array
    {
        $matched = $this->personPrompts
            ->byCriteria(new Criteria(['prompts' => $preparedLines]))
            ->pluck('person_id', 'prompt')
            ->toArray()
        ;

        $unmatched = [];
        foreach (array_unique($preparedLines) as $preparedLine) {
            if (isset($matched[$preparedLine])) {
                continue;
            }

            $unmatched[] = $preparedLine;
        }

        return $matched + $this->identification->match($unmatched);
    }
}
