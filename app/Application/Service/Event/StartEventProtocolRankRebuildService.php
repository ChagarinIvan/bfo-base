<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Bridge\Laravel\Jobs\RebuildPersonRanksJob;
use App\Domain\Event\EventProtocolRepository;
use App\Domain\Event\EventProtocolStatus;
use App\Domain\Event\EventRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Str;
use function count;

final readonly class StartEventProtocolRankRebuildService
{
    public function __construct(private Queue $queue, private EventProtocolRepository $protocolRuns, private EventRepository $events, private ProtocolLineRepository $protocolLines)
    {
    }

    public function execute(StartEventProtocolRankRebuild $command): void
    {
        $protocol = $this->protocolRuns->byId($command->eventProtocolId());

        if ($protocol === null || $protocol->status !== EventProtocolStatus::IDENTIFYING) {
            return;
        }

        $event = $this->events->byId($protocol->event_id);

        if ($event === null || $event->active_event_protocol_id !== $protocol->id) {
            return;
        }

        if ($this->protocolLines->byCriteria($command->unidentifiedLinesCriteria())->isNotEmpty()) {
            return;
        }

        $batchId = (string) Str::uuid();
        $personIds = $this->protocolLines->personIdsForEventProtocol($protocol->id);

        $rankRebuildStarted = $protocol->startRankRebuild($batchId, count($personIds), $command->impression());
        $this->protocolRuns->update($protocol);

        if (! $rankRebuildStarted) {
            return;
        }

        if ($personIds === []) {
            return;
        }

        $this->queue->push(new RebuildPersonRanksJob($personIds, $command->impression(), $protocol->id, $batchId));
    }
}
