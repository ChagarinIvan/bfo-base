<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Event\UpdateEventRanks;
use App\Application\Service\Event\UpdateEventRanksService;
use App\Domain\Event\Event\EventIdentified;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Throwable;

final readonly class UpdateEventRanksHandler implements ShouldQueueAfterCommit
{
    public function __construct(private UpdateEventRanksService $service)
    {
    }

    public function handle(EventIdentified $event): void
    {
        $this->service->execute(new UpdateEventRanks($event->eventId, $event->processingToken, $event->impression));
    }
}
