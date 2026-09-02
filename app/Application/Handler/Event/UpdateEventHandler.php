<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Event\Event\EventUpdated;
use App\Domain\Event\ProtocolStorage;
use App\Services\DistanceService;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpdateEventHandler extends ParseProtocolHandler implements ShouldQueue
{
    use DisableEventHandlerTrait;

    public function __construct(
        ProtocolStorage $storage,
        ParserService $parser,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $identService,
        protected readonly DistanceService $distanceService,
        protected readonly ClearCupCacheService $clearCupCacheService,
        protected readonly RebuildPersonRanksService $rebuildPersonRanksService,
    ) {
        parent::__construct(
            storage: $storage,
            parser: $parser,
            protocolLineService: $protocolLineService,
            identService: $identService,
        );
    }

    public function handle(EventUpdated $systemEvent): void
    {
        if ($systemEvent->withProtocolUpdate) {
            $this->cleanUp($systemEvent->event);
            $this->parse($systemEvent->event->file, $systemEvent->event->id, $systemEvent->event->updated);
        }
    }
}
