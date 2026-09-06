<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Event\Event;
use Illuminate\Support\Collection;

interface ProtocolLineOperations
{
    public function personIdsForEvent(Event $event): array;

    public function deleteEventLines(Event $event): void;

    public function fastIdent(Collection $linesIds): void;

    public function getProtocolLinesInListWithoutPerson(Collection $linesIds): Collection;
}
