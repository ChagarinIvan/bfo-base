<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\Auth\UserId;

final readonly class ExtractPersonFromProtocolLine
{
    public function __construct(
        private string $protocolLineId,
        private UserId $userId,
    ) {
    }

    public function protocolLineId(): int
    {
        return (int) $this->protocolLineId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
