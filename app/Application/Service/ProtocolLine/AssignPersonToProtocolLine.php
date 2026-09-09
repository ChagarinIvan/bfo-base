<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\Auth\UserId;

final readonly class AssignPersonToProtocolLine
{
    public function __construct(
        private string $protocolLineId,
        private string $personId,
        private UserId $userId,
    ) {
    }

    public function protocolLineId(): int
    {
        return (int) $this->protocolLineId;
    }

    public function personId(): int
    {
        return (int) $this->personId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
