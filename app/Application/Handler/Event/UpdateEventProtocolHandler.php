<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Application\Service\Event\ParseEventProtocol;
use App\Application\Service\Event\ParseEventProtocolService;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Distance\DistanceDeleter;
use App\Domain\Event\Event\EventProtocolUpdated;
use App\Domain\Shared\Storage;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpdateEventProtocolHandler implements ShouldQueue
{
    use DisableEventHandlerTrait;

    public function __construct(
        Storage $storage,
        ParserService $parser,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $identService,
        protected readonly DistanceDeleter $distanceDeleter,
        protected readonly ClearCupCacheService $clearCupCacheService,
        protected readonly RebuildPersonRanksService $rebuildPersonRanksService,
    ) {
    }

    public function handle(EventProtocolUpdated $systemEvent): void
    {
        if ($systemEvent->event->active_event_protocol_id === null) {
            return;
        }

        $this->cleanUp($systemEvent->event);

        $this->parser->execute(new ParseEventProtocol(
            $systemEvent->event->file,
            $systemEvent->event->id,
            $systemEvent->event->active_event_protocol_id,
            $systemEvent->event->updated,
        ));
    }
}
