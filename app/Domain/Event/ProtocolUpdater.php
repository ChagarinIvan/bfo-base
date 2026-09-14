<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;

interface ProtocolUpdater
{
    public function update(Event $event, Protocol $protocol, Impression $impression): string;
}
