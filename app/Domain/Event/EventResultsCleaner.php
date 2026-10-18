<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Exception\EventResultsCleanupError;

interface EventResultsCleaner
{
    /** @throws EventResultsCleanupError */
    public function clean(Event $event, Impression $impression): void;
}
