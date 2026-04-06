<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;
use Mav\Slovo\Phonetics;

final readonly class StandardPersonPromptUpdater implements PersonPromptUpdater
{
    public function __construct(
        private Clock $clock,
        private Phonetics $phonetics,
    ) {
    }

    public function update(PersonPrompt $prompt, UpdatePersonPromptInput $input): PersonPrompt
    {
        $prompt->updateData(
            prompt: $input->prompt,
            metaphone: $this->phonetics->metaphour($prompt->prompt),
            impression: new Impression($this->clock->now(), $input->userId),
        );

        return $prompt;
    }
}
