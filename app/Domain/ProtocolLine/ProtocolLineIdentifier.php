<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\ProtocolLine\Exception\IdentifyingError;

interface ProtocolLineIdentifier
{
    /** @throws IdentifyingError */
    public function identify(Event $event, Impression $impression): void;
}
