<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonDto;
use App\Domain\Person\Factory\PersonInput;
use App\Domain\Person\PersonInfo;
use Carbon\Carbon;

final readonly class AddPerson
{
    public function __construct(
        private PersonDto $dto,
        private UserId $userId,
    ) {
    }

    public function personInput(): PersonInput
    {
        return new PersonInput(
            info: $this->info(),
            fromBase: $this->dto->fromBase,
            userId: $this->userId->id,
        );
    }

    private function info(): PersonInfo
    {
        return new PersonInfo(
            firstname: $this->dto->info->firstname,
            lastname: $this->dto->info->lastname,
            birthday: $this->dto->info->birthday ? Carbon::parse($this->dto->info->birthday) : null,
            clubId: $this->dto->info->clubId ? (int) $this->dto->info->clubId : null,
        );
    }
}
