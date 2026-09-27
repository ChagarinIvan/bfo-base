<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\CupEvent\CupEventDto;
use App\Domain\Cup\CupEvent\CupEventUpdateInput;

final readonly class UpdateCupEvent
{
    public function __construct(private string $cupEventId, private CupEventDto $dto, private UserId $userId)
    {
    }

    public function id(): int
    {
        return (int) $this->cupEventId;
    }

    public function input(): CupEventUpdateInput
    {
        return new CupEventUpdateInput($this->dto->eventId, $this->dto->points, $this->userId->id);
    }
}
