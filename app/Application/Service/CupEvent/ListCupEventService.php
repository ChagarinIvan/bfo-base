<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Domain\Cup\CupEvent\CupEventRepository;
use function array_map;

final readonly class ListCupEventService
{
    public function __construct(
        private CupEventRepository $events,
        private CupEventAssembler $assembler,
    ) {
    }

    /** @return ViewCupEventDto[] */
    public function execute(ListCupEvent $command): array
    {
        return array_map(
            $this->assembler->toViewCupEventDto(...),
            $this->events->byCriteria($command->criteria())->all(),
        );
    }
}
