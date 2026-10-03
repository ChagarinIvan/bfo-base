<?php

declare(strict_types=1);

namespace App\Application\Handler\RankCheck;

use App\Application\Service\RankCheck\FailRankCheck;
use App\Application\Service\RankCheck\FailRankCheckService;
use App\Application\Service\RankCheck\ProcessRankCheck;
use App\Application\Service\RankCheck\ProcessRankCheckService;
use App\Domain\RankCheck\Event\RankCheckCreated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\Attributes\Connection;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Connection('redis-rank-checks')]
#[Queue('rank-checks')]
#[Timeout(300)]
#[Tries(3)]
final readonly class RankCheckCreatedHandler implements ShouldQueueAfterCommit
{
    public function __construct(
        private ProcessRankCheckService $service,
        private FailRankCheckService $failure,
    ) {
    }

    public function handle(RankCheckCreated $event): void
    {
        $this->service->execute(new ProcessRankCheck($event->rankCheck->id));
    }

    public function failed(RankCheckCreated $event): void
    {
        $this->failure->execute(new FailRankCheck($event->rankCheck->id));
    }
}
