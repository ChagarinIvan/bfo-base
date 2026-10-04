<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Domain\Event\Event\EventParsingStarted;
use App\Domain\Event\Event\EventProtocolCleaned;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class ParseEventProtocolHandler implements ShouldQueueAfterCommit
{
    public function __construct(private ParseEventProtocolService $parser)
    {
    }

    public function handle(EventParsingStarted|EventProtocolCleaned $event): void
    {
        $this->parser->execute(new ParseEventProtocol(
            $event->eventId,
            $event->processingToken,
            $event->impression->by,
        ));
    }
}
