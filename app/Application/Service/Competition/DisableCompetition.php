<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Auth\UserId;

final readonly class DisableCompetition
{
    public function __construct(
        private string $id,
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
}
