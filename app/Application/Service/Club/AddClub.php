<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Domain\Club\Factory\ClubInput;

final readonly class AddClub
{
    public function __construct(
        private ClubDto $dto,
        private UserId $userId,
    ) {
    }

    public function clubInput(): ClubInput
    {
        return new ClubInput(
            $this->dto->name,
            $this->userId->id,
        );
    }
}
