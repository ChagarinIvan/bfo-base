<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Event\EventProtocol;

interface EventProtocolFactory
{
    public function create(int $eventId, Impression $impression): EventProtocol;
}
