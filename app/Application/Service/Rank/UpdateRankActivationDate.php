<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\ActivationDto;
use Carbon\Carbon;

final readonly class UpdateRankActivationDate
{
    public function __construct(
        private string $id,
        private ActivationDto $dto,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function date(): Carbon
    {
        return Carbon::parse($this->dto->date);
    }
}
