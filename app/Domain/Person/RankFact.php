<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\Rank;
use Carbon\Carbon;

/** Подтверждённое выполнение из протокола либо ручного источника. */
final readonly class RankFact
{
    public function __construct(
        public ?int $protocolLineId,
        public ?int $distanceId,
        public ?int $eventId,
        public ?int $competitionId,
        public Rank $rank,
        public Carbon $achievedOn,
        public ?Carbon $activatedOn,
        public bool $delayedActivation = false,
        public ?string $manualRankId = null,
    ) {
    }
}
