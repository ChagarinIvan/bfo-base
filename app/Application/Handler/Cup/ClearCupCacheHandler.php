<?php

declare(strict_types=1);

namespace App\Application\Handler\Cup;

use App\Application\Service\Cup\ClearCupCache;
use App\Application\Service\Cup\ClearCupCacheService;
use App\Domain\Cup\CupEvent\Event\CupEventCreated;
use App\Domain\Cup\CupEvent\Event\CupEventDisabled;
use App\Domain\Cup\CupEvent\Event\CupEventUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class ClearCupCacheHandler implements ShouldQueue
{
    public function __construct(
        private ClearCupCacheService $service,
    ) {
    }

    public function handle(CupEventCreated|CupEventDisabled|CupEventUpdated $event): void
    {
        $this->service->execute(new ClearCupCache((string) $event->cupEvent->cup_id));
    }
}
