<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Domain\Event\Event\EventCreated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class CreateProtocolHandler implements ShouldQueueAfterCommit
{
    public function __construct(private ParseEventProtocolService $parser) {}

    public function handle(EventCreated $systemEvent): void
    {
        if ($systemEvent->event->file === '' || $systemEvent->event->active_event_protocol_id === null) {
            return;
        }

        $this->parser->execute(new ParseEventProtocol($systemEvent->event->file, $systemEvent->event->id, $systemEvent->event->active_event_protocol_id, $systemEvent->event->created));
    }
}
