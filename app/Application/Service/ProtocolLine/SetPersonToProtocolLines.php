<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Domain\Shared\Criteria;

final readonly class SetPersonToProtocolLines
{
    public function __construct(
        private string $preparedProtocolLine,
        private int $personId,
        private UserId $userId,
    ) {
    }

    public function criteria(): Criteria
    {
        return new Criteria(['preparedLine' => $this->preparedProtocolLine]);
    }

    public function preparedProtocolLine(): string
    {
        return $this->preparedProtocolLine;
    }

    public function personId(): int
    {
        return $this->personId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
