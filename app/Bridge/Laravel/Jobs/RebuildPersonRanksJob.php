<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Jobs;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    public function handle(RebuildPersonRanksService $rebuild): void
    {
        foreach (array_unique($this->personIds) as $personId) {
            $rebuild->execute(new RebuildPersonRanks($personId, new UserId($this->impression->by)));
        }
    }
}
