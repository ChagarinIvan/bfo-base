<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventDto;
use App\Domain\Event\EventRepository;
use function array_map;

final readonly class ListEventsService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @return ViewEventDto[] */
    public function execute(ListEvents $command): array
    {
        $criteria = $command->criteria();
        dump($criteria);
        $all = $this->events->byCriteria($criteria);
        dump($all->get(1));
        dump($all->get(2));
        dump($all->get(3));
        return array_map(
            $this->assembler->toViewEventDto(...),
            $all->all(),
        );
    }
}
