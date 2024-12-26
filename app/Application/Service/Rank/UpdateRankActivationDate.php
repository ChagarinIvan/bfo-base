<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\ActivationDto;
use App\Application\Dto\Rank\UpdateActivationDto;
use Carbon\Carbon;

final readonly class UpdateRankActivationDate
{
    public function __construct(
        private string $id,
        private UpdateActivationDto $dto,
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
