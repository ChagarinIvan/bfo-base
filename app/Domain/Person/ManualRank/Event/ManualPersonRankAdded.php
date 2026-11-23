<?php

declare(strict_types=1);

namespace App\Domain\Person\ManualRank\Event;

use App\Domain\Shared\AggregatedEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class ManualPersonRankAdded extends AggregatedEvent implements ShouldDispatchAfterCommit
{
    public function __construct(public string $manualRankId, public int $personId)
    {
    }
}
