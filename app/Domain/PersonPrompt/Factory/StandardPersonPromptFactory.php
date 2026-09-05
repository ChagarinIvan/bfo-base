<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt\Factory;

use App\Domain\Auth\Impression;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\Shared\Clock;

final readonly class StandardPersonPromptFactory implements PersonPromptFactory
{
    public function __construct(
        private Clock $clock,
        private PersonPromptMetaphone $metaphone,
    ) {
    }

    public function create(PersonPromptInput $input): PersonPrompt
    {
        $prompt = new PersonPrompt();
        $prompt->person_id = $input->personId;
        $prompt->prompt = $input->prompt;
        $prompt->metaphone = $this->metaphone->calculate($prompt->prompt);
        $prompt->created = $prompt->updated = new Impression($this->clock->now(), $input->userId);

        return $prompt;
    }
}
