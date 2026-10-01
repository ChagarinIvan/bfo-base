<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;

interface ProtocolLineIdentifier
{
    /**  */
    public function identify(Event $event, Impression $impression): bool;
}
