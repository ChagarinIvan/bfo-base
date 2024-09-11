<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Domain\Competition\CompetitionInfo;
use Carbon\Carbon;

final readonly class UpdateCompetitionInfo
{
    public function __construct(
        private string $id,
        private CompetitionDto $dto,
        private UserId $userId,
    ) {
    }

    public function info(): CompetitionInfo
    {
        return new CompetitionInfo(
            name: $this->dto->name,
            description: $this->dto->description,
            from: Carbon::parse($this->dto->from),
            to: Carbon::parse($this->dto->to),
        );
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
