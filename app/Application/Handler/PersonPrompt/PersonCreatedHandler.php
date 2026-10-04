<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonCreated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class PersonCreatedHandler extends AbstractCreatePersonPromptsHandler implements ShouldQueueAfterCommit
{
    public function handle(PersonCreated $event): void
    {
        $this->process($event->person);
    }
}
