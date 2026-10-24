<?php

declare(strict_types=1);

namespace App\Application\Handler\Rank;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class ProtocolLineRankActivatedHandler implements ShouldQueueAfterCommit
{
    public function __construct(private RebuildPersonRanksService $service)
    {
    }

    public function handle(ProtocolLineRankActivated $event): void
    {
        if ($event->personId) {
            try {
                $this->service->execute(new RebuildPersonRanks($event->personId, new UserId($event->impression->by), RankAuditTrigger::Activation));
            } catch (PersonNotFound) {
                // The queued activation no longer has a person to update.
            }
        }
    }
}
