<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListCupEventService
{
    public function __construct(
        private CupEventRepository $events,
        private CupEventAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewCupEventDto> */
    public function execute(ListCupEvent $command): Slice
    {
        return $this->events
            ->paginate($command->criteria())
            ->map($this->assembler->toViewCupEventDto(...))
        ;
    }
}
