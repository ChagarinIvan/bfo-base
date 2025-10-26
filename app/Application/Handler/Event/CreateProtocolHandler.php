<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CreateProtocolHandler extends ParseProtocolHandler implements ShouldQueue
{
    public function handle(EventCreated $systemEvent): void
    {
        $this->parse($systemEvent->event->file, $systemEvent->event->id);
    }
}
