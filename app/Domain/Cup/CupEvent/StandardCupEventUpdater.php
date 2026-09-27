<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

use App\Domain\Auth\Impression;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Exception\EventNotExists;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;

final readonly class StandardCupEventUpdater implements CupEventUpdater
{
    public function __construct(
        private CupEventRepository $cupEvents,
        private EventRepository $events,
        private Clock $clock,
    ) {
    }

    /** @throws CupAlreadyContainsEvent|EventNotExists */
    public function update(CupEvent $cupEvent, CupEventUpdateInput $input): CupEvent
    {
        $this->events->byId($input->eventId) ?? throw new EventNotExists();

        if ($cupEvent->event_id !== $input->eventId && $this->cupEvents
            ->byCriteria(new Criteria([
                'cupId' => (string) $cupEvent->cup_id,
                'eventIds' => [(string) $input->eventId],
            ]))
            ->contains(static fn (CupEvent $existing): bool => $existing->id !== $cupEvent->id)) {
            throw new CupAlreadyContainsEvent();
        }

        $cupEvent->updateData(
            $input->eventId,
            $input->points,
            new Impression($this->clock->now(), $input->userId),
        );

        return $cupEvent;
    }
}
