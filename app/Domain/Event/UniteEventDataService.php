<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface UniteEventDataService
{
    /** @param iterable<int, Event> $events */
    public function execute(iterable $events, Event $newEvent): void;
}
