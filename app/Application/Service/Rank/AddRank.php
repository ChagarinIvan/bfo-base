<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankDto;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Rank\RankType;
use App\Domain\Shared\Footprint;
use Carbon\Carbon;
use DateTimeImmutable;

final readonly class AddRank
{
    public function __construct(
        private RankDto $dto,
        private string $userId,
    ) {
    }

    public function rankInput(): RankInput
    {
        return new RankInput(
            personId: (int) $this->dto->personId,
            type: RankType::fromString($this->dto->type),
            completedAt: Carbon::createFromFormat(DateTimeImmutable::ATOM, $this->dto->completedAt),
            by: $this->footprint(),
            eventId: $this->dto->eventId ? ((int) $this->dto->eventId) : null,
        );
    }

    private function footprint(): Footprint
    {
        return new Footprint((int) $this->userId);
    }
}
