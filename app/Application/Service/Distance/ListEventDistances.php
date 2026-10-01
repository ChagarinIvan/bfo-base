<?php

declare(strict_types=1);

namespace App\Application\Service\Distance;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Distance\DistanceSearchDto;
use App\Domain\Shared\Criteria;

final readonly class ListEventDistances
{
    public function __construct(private DistanceSearchDto $search, private ?UserId $userId = null)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(['eventId' => $this->search->eventId]);
    }

    public function eventId(): int
    {
        return (int) $this->search->eventId;
    }

    public function isGuest(): bool
    {
        return $this->userId === null;
    }
}
