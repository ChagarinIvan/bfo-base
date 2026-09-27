<?php

declare(strict_types=1);

namespace App\Application\Handler\RankCheck;

use App\Application\Service\RankCheck\ProcessRankCheck;
use App\Application\Service\RankCheck\ProcessRankCheckService;
use App\Domain\RankCheck\Event\RankCheckCreated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class RankCheckCreatedHandler implements ShouldQueueAfterCommit
{
    public function __construct(private ProcessRankCheckService $service)
    {
    }

    public function handle(RankCheckCreated $event): void
    {
        $this->service->execute(new ProcessRankCheck($event->rankCheck->id));
    }
}
