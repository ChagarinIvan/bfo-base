<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Event\EventProtocolRepository;

final readonly class RecordEventProtocolLineIdentificationService
{
    public function __construct(
        private EventProtocolRepository $protocolRuns,
        private StartEventProtocolRankRebuildService $rankRebuild,
    ) {
    }

    public function execute(RecordEventProtocolLineIdentification $command): void
    {
        $protocol = $this->protocolRuns->byId($command->eventProtocolId());

        if ($protocol === null) {
            return;
        }

        if (!$protocol->recordIdentifiedLine($command->protocolLineId(), $command->impression())) {
            return;
        }

        $this->protocolRuns->update($protocol);

        $this->rankRebuild->execute(new StartEventProtocolRankRebuild(
            $protocol->id,
            $command->impression(),
        ));
    }
}
