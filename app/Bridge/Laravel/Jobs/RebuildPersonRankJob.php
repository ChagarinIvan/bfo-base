<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Jobs;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Person\RankCalculationReason;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RebuildPersonRankJob implements ShouldQueue
{
    use Queueable;

    public RankCalculationReason $reason = RankCalculationReason::Manual;

    public function __construct(
        public readonly int $personId,
        public readonly Impression $impression,
        RankCalculationReason $reason = RankCalculationReason::Manual,
    ) {
        $this->reason = $reason;
    }

    public function handle(RebuildPersonRanksService $rebuild): void
    {
        try {
            $rebuild->execute(new RebuildPersonRanks($this->personId, new UserId($this->impression->by), $this->reason));
        } catch (PersonNotFound) {
            // The person was removed after this asynchronous job was queued.
        }
    }
}
