<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Event\Event\EventDisabled;
use App\Services\DistanceService;
use App\Services\ProtocolLineService;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class DisableEventHandler implements ShouldQueue
{
    use DisableEventHandlerTrait;

    public function __construct(
        private ProtocolLineService $protocolLineService,
        private DistanceService $distanceService,
        private ClearCupCacheService $clearCupCacheService,
        private RebuildPersonRanksService $rebuildPersonRanksService,
    ) {
    }

    public function handle(EventDisabled $event): void
    {
        $this->cleanUp($event->event);
    }
}
