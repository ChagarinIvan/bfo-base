<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Shared\Clock;

final readonly class StandardCupEventFactory implements CupEventFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(CupEventInput $input): CupEvent
    {
        $cupEvent = new CupEvent();
        $cupEvent->cup_id = $input->cupId;
        $cupEvent->event_id = $input->eventId;
        $cupEvent->points = $input->points;
        $cupEvent->active = true;
        $cupEvent->created = $cupEvent->updated = new Impression($this->clock->now(), $input->userId);

        return $cupEvent;
    }
}
