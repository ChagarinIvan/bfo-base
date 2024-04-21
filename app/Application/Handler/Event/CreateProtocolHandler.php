<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventCreated;

final readonly class CreateProtocolHandler extends ParseProtocolHandler
{
    public function handle(EventCreated $systemEvent): void
    {
        $this->parse($systemEvent->event->file, $systemEvent->event->id);
    }
}
