<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Event\Event;

interface ProtocolLineOperations
{
    public function personIdsForEvent(Event $event): array;

    public function deleteEventLines(Event $event): void;

    /** @param list<int> $linesIds */
    public function fastIdent(array $linesIds): void;

    /** @param list<int> $linesIds
     * @return list<ProtocolLine>
     */
    public function getProtocolLinesInListWithoutPerson(array $linesIds): array;
}
