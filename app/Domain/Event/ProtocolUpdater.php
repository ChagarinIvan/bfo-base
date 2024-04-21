<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface ProtocolUpdater
{
    public function update(Event $event, Protocol $protocol): string;
}
