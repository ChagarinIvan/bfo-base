<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Factory\EventInput;
use App\Domain\Event\Protocol\ProtocolSource;
use Carbon\Carbon;

final readonly class AddEvent
{
    public function __construct(
        private int $competitionId,
        private EventDto $event,
        private EventProtocolDto $protocol,
        private UserId $userId,
    ) {
    }

    public function eventInput(): EventInput
    {
        return new EventInput(
            $this->info(),
            $this->competitionId,
            $this->userId->id,
        );
    }

    public function protocolSource(): ProtocolSource
    {
        return new ProtocolSource($this->protocol->protocol, $this->protocol->url);
    }

    private function info(): EventInfo
    {
        return new EventInfo(
            name: $this->event->info->name,
            description: $this->event->info->description,
            date: Carbon::parse($this->event->info->date),
        );
    }
}
