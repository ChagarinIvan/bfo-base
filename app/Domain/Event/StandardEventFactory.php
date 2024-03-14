<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;
use App\Models\Event;

final readonly class StandardEventFactory implements EventFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(EventInput $input): Event
    {
        $event = new Event();
        $event->competition_id = $input->competitionId;
        $event->name = $input->info->name;
        $event->description = $input->info->description;
        $event->date = $input->info->date;
        $event->created = $event->updated = new Impression($this->clock->now(), $input->userId);

        return $event;
    }
}
