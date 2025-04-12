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

final readonly class UpdateEventHandler extends ParseProtocolHandler implements ShouldQueue
{
    use DisableEventHandlerTrait;

    public function __construct(
        protected ProtocolStorage $storage,
        protected ParserService $parser,
        protected ProtocolLineService $protocolLineService,
        protected ProtocolLineIdentService $identService,
        protected RankService $ranksService,
        protected DistanceService $distanceService,
        protected CupsService $cupsService,
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
            $this->parse($systemEvent->event->file, $systemEvent->event->id);
        }
    }
}
