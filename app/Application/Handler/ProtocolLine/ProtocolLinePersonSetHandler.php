<?php

declare(strict_types=1);

namespace App\Application\Handler\ProtocolLine;

use App\Application\Service\Event\RecordEventProtocolLineIdentification;
use App\Application\Service\Event\RecordEventProtocolLineIdentificationService;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonSet;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class ProtocolLinePersonSetHandler implements ShouldQueueAfterCommit
{
    public function __construct(private RecordEventProtocolLineIdentificationService $identification)
    {
    }

    public function handle(ProtocolLinePersonSet $event): void
    {
        $eventProtocolId = $event->protocolLine->event_protocol_id;

        if ($eventProtocolId === null) {
            return;
        }

        $this->identification->execute(new RecordEventProtocolLineIdentification(
            $eventProtocolId,
            $event->protocolLine->id,
            $event->impression,
        ));
    }
}
