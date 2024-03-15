<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventDto;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Factory\EventInput;
use Carbon\Carbon;

final readonly class AddEvent
{
    public function __construct(
        private EventDto $dto,
        private UserId $userId,
    ) {
    }

    public function eventInput(): EventInput
    {
        return new EventInput(
            $this->info(),
            (int) $this->dto->competitionId,
            $this->userId->id,
        );
    }

    private function info(): EventInfo
    {
        return new EventInfo(
            name: $this->dto->info->name,
            description: $this->dto->info->description ?? '',
            date: Carbon::parse($this->dto->info->date),
        );
    }
}
