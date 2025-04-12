<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonInfoUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class PersonInfoUpdatedHandler extends CreatePersonPrompts implements ShouldQueue
{
    public function handle(PersonInfoUpdated $event): void
    {
        $this->process($event->person);
    }
}
