<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Jobs;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventProtocolRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RebuildPersonRankJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $personId,
        public readonly Impression $impression,
        public readonly ?int $eventProtocolId = null,
        public readonly ?string $rankBatchId = null,
    ) {
    }

    public function handle(RebuildPersonRanksService $rebuild, EventProtocolRepository $protocolRuns): void
    {
        try {
            $rebuild->execute(new RebuildPersonRanks($this->personId, new UserId($this->impression->by)));
        } catch (PersonNotFound) {
            // The person was removed after this asynchronous job was queued.
        }

        if ($this->eventProtocolId !== null && $this->rankBatchId !== null) {
            $protocol = $protocolRuns->byId($this->eventProtocolId);

            if ($protocol === null) {
                return;
            }

            $protocol->completeRankJob($this->rankBatchId, $this->personId, $this->impression);
            $protocolRuns->update($protocol);
        }
    }
}
