<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\UniteEventsDto;
use App\Domain\Shared\Criteria;

final readonly class UniteEvents
{
    public function __construct(
        private int $competitionId,
        private UniteEventsDto $input,
        private UserId $userId,
    )
    {
    }

    public function competitionId(): int
    {
        return $this->competitionId;
    }

    public function criteria(): Criteria
    {
        return new Criteria([
            'competitionId' => $this->competitionId,
            'ids' => $this->eventIds(),
        ]);
    }

    /** @return list<int> */
    public function eventIds(): array
    {
        return $this->input->eventIds;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
