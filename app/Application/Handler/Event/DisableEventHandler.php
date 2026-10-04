<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Event\CleanupEventResults;
use App\Application\Service\Event\CleanupEventResultsService;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Event\Event\EventProcessingFailed;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class DisableEventHandler implements ShouldQueueAfterCommit
{
    public function __construct(private CleanupEventResultsService $cleanup)
    {
    }

    public function handle(EventDisabled|EventProcessingFailed $event): void
    {
        $this->cleanup->execute(new CleanupEventResults(
            $event->eventId,
            $event->processingToken,
        ));
    }
}
