<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Event;

use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedEvent;

final readonly class ProtocolLinePersonAssigned extends AggregatedEvent
{
    public int $newPersonId;
    public string $preparedLine;

    public function __construct(
        public ProtocolLine $protocolLine,
        public ?int $oldPersonId,
        public Impression $impression,
    ) {
        $this->newPersonId = (int) $protocolLine->person_id;
        $this->preparedLine = $protocolLine->prepared_line;
    }
}
