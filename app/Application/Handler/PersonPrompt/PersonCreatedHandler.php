<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonCreated;

final readonly class PersonCreatedHandler extends CreatePersonPrompts
{
    public function handle(PersonCreated $event): void
    {
        $this->process($event->person);
    }
}
