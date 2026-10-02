<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Event\Event;

interface ProtocolLineOperations
{
    public function personIdsForEvent(Event $event): array;

    public function deleteEventLines(Event $event): void;

    public function fastIdentByEvent(Event $event): void;

    public function activateEventLines(Event $event): void;
}
