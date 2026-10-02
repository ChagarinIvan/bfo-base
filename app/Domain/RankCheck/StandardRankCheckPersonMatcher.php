<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\PromptIdentifier;
use App\Domain\Shared\Criteria;
use App\Services\ProtocolLineIdentService;

final readonly class StandardRankCheckPersonMatcher implements RankCheckPersonMatcher
{
    public function __construct(
        private PersonPromptRepository $personPrompts,
        private PromptIdentifier $identification,
    ) {
    }

    public function match(array $preparedLines): array
    {
        $matched = $this->personPrompts
            ->byCriteria(new Criteria(['prompts' => $preparedLines]))
            ->pluck('person_id', 'prompt')
            ->toArray()
        ;

        foreach ($preparedLines as $preparedLine) {
            if (isset($matched[$preparedLine])) {
                continue;
            }

            $personId = $this->identification->identPerson($preparedLine);
            if ($personId) {
                $matched[$preparedLine] = $personId;
            }
        }

        return $matched;
    }
}
