<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Auth\Impression;

final readonly class IdentifyProtocolLines
{
    public function __construct(public int $eventId, public string $processingToken, public Impression $impression)
    {
    }
}
