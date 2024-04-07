<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Factory\EventInput;
use App\Domain\Event\Protocol;
use Carbon\Carbon;

final readonly class AddEvent
{
    public function __construct(
        private EventDto $event,
        private EventProtocolDto $protocol,
        private UserId $userId,
    ) {
    }

    public function eventInput(): EventInput
    {
        return new EventInput(
            $this->info(),
            (int) $this->event->competitionId,
            $this->userId->id,
        );
    }

    public function protocolInput(): Protocol
    {
        return new Protocol(
            $this->protocol->content,
            $this->protocol->extension,
        );
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
