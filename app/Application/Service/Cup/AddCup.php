<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Domain\Cup\CupInfo;
use App\Domain\Cup\CupType;
use App\Domain\Cup\Factory\CupInput;
use App\Models\Year;

final readonly class AddCup
{
    public function __construct(
        private CupDto $dto,
        private UserId $userId,
    ) {
    }

    public function cupInput(): CupInput
    {
        return new CupInput(
            new CupInfo(
                name: $this->dto->name,
                eventsCount: $this->dto->eventsCount,
                year: Year::from($this->dto->year),
                type: CupType::from($this->dto->type),
            ),
            visible: $this->dto->visible,
            userId: $this->userId->id,
        );
    }
}
