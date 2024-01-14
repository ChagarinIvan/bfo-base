<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Rank\RankId;
use App\Domain\Shared\Footprint;
use Carbon\Carbon;
use DateTimeImmutable;

final readonly class ActivateRank
{
    public function __construct(
        private string $id,
        private string $startDate,
        private string $userId,
    ) {
    }

    public function footprint(): Footprint
    {
        return new Footprint((int) $this->userId);
    }

    public function id(): RankId
    {
        return RankId::fromString($this->id);
    }

    public function startDate(): Carbon
    {
        return Carbon::createFromFormat(DateTimeImmutable::ATOM, $this->startDate);
    }
}
