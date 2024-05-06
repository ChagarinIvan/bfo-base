<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventDisabled;
use App\Services\CupsService;
use App\Services\DistanceService;
use App\Services\ProtocolLineService;
use App\Services\RankService;

final readonly class DisableEventHandler
{
    use DisableEventHandlerTrait;

    public function __construct(
        private RankService $ranksService,
        private ProtocolLineService $protocolLineService,
        private DistanceService $distanceService,
        private CupsService $cupsService,
    ) {
    }

    public function handle(EventDisabled $event): void
    {
        $this->cleanUp($event->event);
    }
}
