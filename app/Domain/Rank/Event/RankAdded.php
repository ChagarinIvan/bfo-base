<?php

declare(strict_types=1);

namespace App\Domain\Rank\Event;

use App\Domain\Auth\Impression;
use App\Domain\Rank\RankId;
use App\Domain\Rank\RankType;
use Carbon\Carbon;

final class RankAdded
{
    public function __construct(
        public readonly RankId $id,
        public readonly int $personId,
        public readonly ?int $eventId,
        public readonly RankType $type,
        public readonly Carbon $completedAt,
        public readonly Impression $created,
    ) {
    }
}
