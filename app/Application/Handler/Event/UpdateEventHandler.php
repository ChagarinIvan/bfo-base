<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventUpdated;
use App\Domain\Event\ProtocolStorage;
use App\Services\CupsService;
use App\Services\DistanceService;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Contracts\Queue\ShouldQueue;

final class UpdateEventHandler extends ParseProtocolHandler implements ShouldQueue
{
    use DisableEventHandlerTrait;

    public function __construct(
        ProtocolStorage $storage,
        ParserService $parser,
        ProtocolLineService $protocolLineService,
        ProtocolLineIdentService $identService,
        protected readonly RankService $ranksService,
        protected readonly DistanceService $distanceService,
        protected readonly CupsService $cupsService,
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
//            $this->parse($systemEvent->event->file, $systemEvent->event->id);
        }
    }
}
