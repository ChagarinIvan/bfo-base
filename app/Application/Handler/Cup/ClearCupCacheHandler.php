<?php

declare(strict_types=1);

namespace App\Application\Handler\Cup;

use App\Domain\Cup\CupEvent\Event\CupEventCreated;
use App\Domain\Cup\CupEvent\Event\CupEventDisabled;
use App\Domain\Cup\CupEvent\Event\CupEventUpdated;
use App\Services\CupsService;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class ClearCupCacheHandler implements ShouldQueue
{
    public function __construct(
        protected CupsService $service,
    ) {
    }

    public function handle(CupEventCreated|CupEventDisabled|CupEventUpdated $event): void
    {
        $this->service->clearCupCache($event->cupEvent->cup_id);
    }
}
