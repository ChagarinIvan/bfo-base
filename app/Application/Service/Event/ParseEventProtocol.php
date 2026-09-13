<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Auth\Impression;

final readonly class ParseEventProtocol
{
    public function __construct(
        private string $path,
        private int $eventId,
        private int $eventProtocolId,
        private Impression $impression,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function eventId(): int
    {
        return $this->eventId;
    }

    public function eventProtocolId(): int
    {
        return $this->eventProtocolId;
    }

    public function impression(): Impression
    {
        return $this->impression;
    }
}
