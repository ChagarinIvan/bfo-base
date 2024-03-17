<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonInfoUpdated;

final readonly class PersonInfoUpdatedHandler extends CreatePersonPrompts
{
    public function handle(PersonInfoUpdated $event): void
    {
        $this->process($event->person);
    }
}
