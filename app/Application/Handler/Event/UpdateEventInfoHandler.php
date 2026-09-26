<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Domain\Event\Event\EventInfoUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class UpdateEventInfoHandler implements ShouldQueue
{
    public function __construct(private ClearCupCacheService $clearCupCacheService)
    {
    }

    public function handle(EventInfoUpdated $systemEvent): void
    {
        if ($systemEvent->event->cups->isNotEmpty()) {
            $this->clearCupCacheService->execute();
        }
    }
}
