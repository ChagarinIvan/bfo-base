<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class PersonCreatedHandler extends CreatePersonPrompts implements ShouldQueue
{
    public function handle(PersonCreated $event): void
    {
        $this->process($event->person);
    }
}
