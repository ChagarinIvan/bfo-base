<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt\Event;

use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\Shared\AggregatedEvent;

final readonly class PersonPromptCreated extends AggregatedEvent
{
    public function __construct(public PersonPrompt $prompt)
    {
    }
}
