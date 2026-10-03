<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Factory;

use App\Domain\Event\Event;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineInput;

interface ProtocolLinesFactory
{
    /**
     * @param list<ProtocolLineInput> $inputs
     * @return list<ProtocolLine>
     */
    public function create(Event $event, array $inputs): array;
}
