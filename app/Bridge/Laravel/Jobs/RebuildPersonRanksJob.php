<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Jobs;

use App\Domain\Auth\Impression;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Foundation\Queue\Queueable;
use function array_unique;

final class RebuildPersonRanksJob implements ShouldQueue
{
    use Queueable;

    /** @param list<int> $personIds */
    public function __construct(
        public readonly array $personIds,
        public readonly Impression $impression,
    )
    {
    }

    public function handle(Queue $queue): void
    {
        foreach (array_unique($this->personIds) as $personId) {
            $queue->push(new RebuildPersonRankJob($personId, $this->impression));
        }
    }
}
