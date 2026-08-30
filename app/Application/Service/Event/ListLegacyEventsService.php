<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\LegacyViewEventDto;
use App\Domain\Event\EventRepository;
use function array_map;

final readonly class ListLegacyEventsService
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $assembler,
    ) {
    }

    /** @return LegacyViewEventDto[] */
    public function execute(ListEvents $command): array
    {
        return array_map(
            $this->assembler->toLegacyViewEventDto(...),
            $this->events->byCriteria($command->criteria())->all(),
        );
    }
}
