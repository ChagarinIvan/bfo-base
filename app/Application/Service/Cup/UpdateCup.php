<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupDto;
use App\Application\Dto\Event\UpdateEventDto;
use App\Domain\Cup\CupInfo;
use App\Domain\Cup\CupType;
use App\Domain\Cup\Factory\CupInput;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Protocol;
use App\Domain\Event\UpdateInput;
use App\Models\Year;
use Carbon\Carbon;

final readonly class UpdateCup
{
    public function __construct(
        private string $id,
        private CupDto $dto,
        private UserId $userId,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function userId(): int
    {
        return $this->userId->id;
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
