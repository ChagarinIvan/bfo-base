<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionDto;
use App\Domain\Competition\CompetitionInput;
use Carbon\Carbon;

final readonly class AddCompetition
{
    public function __construct(
        private CompetitionDto $dto,
        private int $userId,
    ) {
    }

    public function competitionInput(): CompetitionInput
    {
        return new CompetitionInput(
            name: $this->dto->name,
            description: $this->dto->description,
            from: Carbon::parse($this->dto->from),
            to: Carbon::parse($this->dto->to),
            userId: $this->userId,
        );
    }
}
