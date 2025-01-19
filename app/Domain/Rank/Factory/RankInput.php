<?php

declare(strict_types=1);

namespace App\Domain\Rank\Factory;

use Carbon\Carbon;

final readonly class RankInput
{
    public function __construct(
        public int $personId,
        public ?int $eventId,
        public string $rank,
        public Carbon $startDate,
        public ?Carbon $activatedDate,
        public ?Carbon $finishDate = null,
    ) {
    }
}
