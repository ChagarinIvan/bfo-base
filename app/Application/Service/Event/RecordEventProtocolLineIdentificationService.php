<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Event\EventProtocolRepository;
use App\Domain\Event\EventRepository;

final readonly class RecordEventProtocolLineIdentificationService
{
    public function __construct(
        private EventProtocolRepository $protocolRuns,
        private EventRepository $events,
        private StartEventProtocolRankRebuildService $rankRebuild,
    ) {
    }

    public function execute(RecordEventProtocolLineIdentification $command): void
    {
        $protocol = $this->protocolRuns->byId($command->eventProtocolId());

        if ($protocol === null) {
            return;
        }

        $event = $this->events->byId($protocol->event_id);

        if ($event === null || $event->active_event_protocol_id !== $protocol->id) {
            return;
        }

        $lineRecorded = $protocol->recordIdentifiedLine($command->protocolLineId(), $command->impression());
        $this->protocolRuns->update($protocol);

        if (! $lineRecorded) {
            return;
        }

        $this->rankRebuild->execute(new StartEventProtocolRankRebuild(
            $protocol->id,
            $command->impression(),
        ));
    }
}
