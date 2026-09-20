<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CreateCupEventDto;
use App\Domain\Cup\CupEvent\Factory\CupEventInput;

final readonly class AddCupEvent
{
    public function __construct(private CreateCupEventDto $dto, private UserId $userId)
    {
    }

    public function cupId(): int
    {
        return $this->dto->cupId;
    }

    public function eventId(): int
    {
        return $this->dto->eventId;
    }

    public function input(): CupEventInput
    {
        return new CupEventInput($this->dto->cupId, $this->dto->eventId, $this->dto->points, $this->userId->id);
    }
}
