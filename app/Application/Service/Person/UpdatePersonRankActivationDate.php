<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use Carbon\Carbon;

final readonly class UpdatePersonRankActivationDate
{
    public function __construct(
        private string $id,
        private UpdatePersonRankActivationDateDto $dto,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function date(): ?Carbon
    {
        return $this->dto->date ? Carbon::parse($this->dto->date) : null;
    }
}
