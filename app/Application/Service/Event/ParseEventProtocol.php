<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

final readonly class ParseEventProtocol
{
    public function __construct(public int $eventId, public string $processingToken, public int $userId)
    {
    }
}
