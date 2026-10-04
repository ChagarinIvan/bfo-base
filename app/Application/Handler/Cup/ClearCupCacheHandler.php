<?php

declare(strict_types=1);

namespace App\Application\Handler\Cup;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Domain\Cup\CupEvent\Event\CupEventCreated;
use App\Domain\Cup\CupEvent\Event\CupEventDisabled;
use App\Domain\Cup\CupEvent\Event\CupEventUpdated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class ClearCupCacheHandler implements ShouldQueueAfterCommit
{
    public function __construct(
        private ClearCupCacheService $service,
    ) {
    }

    public function handle(CupEventCreated|CupEventDisabled|CupEventUpdated $event): void
    {
        $this->service->execute();
    }
}
