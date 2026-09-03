<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use Carbon\Carbon;

final readonly class UpdatePersonRankActivationDate
{
    public function __construct(
        private string $id,
        private UpdatePersonRankActivationDateDto $dto,
        private UserId $userId,
    ) {
    }

    public function protocolLine(): int
    {
        return (int) $this->id;
    }

    public function date(): ?Carbon
    {
        return $this->dto->date ? Carbon::parse($this->dto->date) : null;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
