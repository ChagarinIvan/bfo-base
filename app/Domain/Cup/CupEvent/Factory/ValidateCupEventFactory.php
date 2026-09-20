<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent\Factory;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Exception\CupNotExists;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Exception\EventNotExists;
use App\Domain\Shared\Criteria;

final readonly class ValidateCupEventFactory implements CupEventFactory
{
    public function __construct(
        private CupEventFactory $decorated,
        private CupRepository $cups,
        private EventRepository $events,
        private CupEventRepository $cupEvents,
    ) {
    }

    public function create(CupEventInput $input): CupEvent
    {
        $this->cups->byId($input->cupId) ?? throw new CupNotExists();
        $this->events->byId($input->eventId) ?? throw new EventNotExists();

        $cupEvents = $this->cupEvents->byCriteria(new Criteria([
            'cupId' => (string)$input->cupId,
            'eventIds' => [(string)$input->eventId],
        ]));

        if ($cupEvents->isNotEmpty()) {
            throw new CupAlreadyContainsEvent();
        }

        return $this->decorated->create($input);
    }
}
