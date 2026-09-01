<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Jobs;

use App\Application\Service\Rank\RebuildPersonRanks;
use App\Application\Service\Rank\RebuildPersonRanksService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use function array_unique;

final class RebuildPersonRanksJob implements ShouldQueue
{
    use Queueable;

    /** @param list<int> $personIds */
    public function __construct(public readonly array $personIds)
    {
    }

    public function handle(RebuildPersonRanksService $rebuild): void
    {
        foreach (array_unique($this->personIds) as $personId) {
            $rebuild->execute(new RebuildPersonRanks($personId));
        }
    }
}
