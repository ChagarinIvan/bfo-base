<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Event\IdentifyProtocolLines;
use App\Application\Service\Event\IdentifyProtocolLinesService;
use App\Domain\Event\Event\EventParsed;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class IdentifyProtocolLinesHandler implements ShouldQueueAfterCommit
{
    public function __construct(private IdentifyProtocolLinesService $service)
    {
    }

    public function handle(EventParsed $event): void
    {
        $this->service->execute(new IdentifyProtocolLines($event->eventId, $event->processingToken, $event->impression));
    }
}
