<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Domain\Person\Event\PersonInfoUpdated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class PersonInfoUpdatedHandler extends AbstractCreatePersonPromptsHandler implements ShouldQueueAfterCommit
{
    public function handle(PersonInfoUpdated $event): void
    {
        $this->process($event->person);
    }
}
