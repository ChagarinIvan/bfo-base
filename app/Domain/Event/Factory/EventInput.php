<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Event\EventInfo;
use App\Domain\Event\Protocol;

final readonly class EventInput
{
    public function __construct(
        public EventInfo $info,
        public int $competitionId,
        public int $userId,
        public Protocol $protocol,
        public ?string $file = null,
    ) {
    }

    public function withFile(string $file): self
    {
        return new self($this->info, $this->competitionId, $this->userId, $this->protocol, $file);
    }
}
