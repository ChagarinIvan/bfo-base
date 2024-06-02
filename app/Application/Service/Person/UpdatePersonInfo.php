<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\PersonInfoDto;
use App\Domain\Person\Citizenship;
use App\Domain\Person\PersonInfo;
use Carbon\Carbon;

final readonly class UpdatePersonInfo
{
    public function __construct(
        private string $id,
        private PersonInfoDto $dto,
        private UserId $userId,
    ) {
    }

    public function info(): PersonInfo
    {
        return new PersonInfo(
            firstname: $this->dto->firstname,
            lastname: $this->dto->lastname,
            birthday: $this->dto->birthday ? Carbon::parse($this->dto->birthday) : null,
            citizenship: Citizenship::from($this->dto->citizenship),
            clubId: $this->dto->clubId ? (int) $this->dto->clubId : null,
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
