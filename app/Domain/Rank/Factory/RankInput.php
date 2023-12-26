<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use App\Domain\Rank\RankType;
use App\Domain\Shared\Footprint;
use Carbon\Carbon;

final readonly class RankInput
{
    public function __construct(
        public int $personId,
        public RankType $type,
        public Carbon $completedAt,
        public Footprint $by,
        public ?int $eventId = null,
    ) {
    }
}
