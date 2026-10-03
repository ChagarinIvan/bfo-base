<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Exception\EventParsingError;
use App\Domain\ProtocolLine\ProtocolLineInput;

interface ProtocolParser
{
    /**
     * @throws EventParsingError
     * @return list<ProtocolLineInput>
     */
    public function parse(Event $event): array;
}
