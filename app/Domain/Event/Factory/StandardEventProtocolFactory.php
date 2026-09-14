<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Event\EventProtocol;
use Illuminate\Support\Str;

final class StandardEventProtocolFactory implements EventProtocolFactory
{
    public function create(int $eventId, Impression $impression): EventProtocol
    {
        $protocol = EventProtocol::queue($eventId, (string) Str::uuid());
        $protocol->created = $impression;
        $protocol->updated = $impression;

        return $protocol;
    }
}
