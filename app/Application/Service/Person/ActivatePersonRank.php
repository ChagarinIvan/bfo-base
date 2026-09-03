<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ActivatePersonRankDto;
use Carbon\Carbon;

final readonly class ActivatePersonRank
{
    public function __construct(
        private string $id,
        private ActivatePersonRankDto $dto,
        private UserId $userId,
    ) {
    }

    public function protocolLine(): int
    {
        return (int) $this->id;
    }

    public function date(): Carbon
    {
        return Carbon::parse($this->dto->date);
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
