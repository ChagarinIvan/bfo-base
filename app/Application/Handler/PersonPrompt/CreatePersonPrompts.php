<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Person;
use App\Services\PersonPromptService;
use App\Services\ProtocolLineIdentService;
use function array_diff;
use function implode;
use function mb_strtolower;

readonly class CreatePersonPrompts
{
    public function __construct(private PersonPromptService $promptService)
    {
    }

    public function process(Person $person): void
    {
        $existPrompts = $person->prompts->pluck('prompt')->toArray();
        $prompts = [];

        $hasNameSake = Person::where('active', true)->whereFirstname($person->firstname)->whereLastname($person->lastname)->count() > 1;

        $personData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
        ];

        $reversPersonData = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->firstname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($person->lastname)),
        ];

        if ($hasNameSake) {
            $this->promptService->deletePrompt(implode('_', $personData));
            $this->promptService->deletePrompt(implode('_', $reversPersonData));
        } else {
            $prompts[] = implode('_', $personData);
            $prompts[] = implode('_', $reversPersonData);
        }

        if ($person->birthday !== null) {
            $personData[] = $person->birthday->format('Y');
            $reversPersonData[] = $person->birthday->format('Y');
            $prompts[] = implode('_', $personData);
            $prompts[] = implode('_', $reversPersonData);
        }

        $prompts = array_diff($prompts, $existPrompts);
        foreach ($prompts as $prompt) {
            $this->promptService->storePrompt($prompt, $person->id);
        }
    }
}
