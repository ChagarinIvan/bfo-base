<?php

declare(strict_types=1);

namespace App\Domain\Event\Event;

use App\Domain\Event\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class EventDisabled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Event $event)
    {
    }
}
