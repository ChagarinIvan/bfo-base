<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\CupEvent\ViewCupEventPointDto;
use App\Application\Dto\Cup\ViewCalculatedCupEventDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupRepository;

final readonly class CalculateCupEventService
{
    public function __construct(
        private CupRepository $cups,
        private CupEventRepository $cupEvents,
        private CupAssembler $assembler,
    ) {
    }

    /**
     * @throws CupEventNotFound
     * @throws CupNotFound
     * @throws GroupNotFound
     */
    public function execute(CalculateCupEvent $command): ViewCalculatedCupEventDto
    {
        $cup = $this->cups->byId($command->cupId()) ?? throw new CupNotFound;
        $cupEvent = $this->cupEvents->byId($command->eventId()) ?? throw new CupEventNotFound;
        $points = $cup->calculateEvent($cupEvent, $command->cupGroup());

        return $this->assembler->toViewCalculatedCupEventDto($cup, $cupEvent, $points->all());
    }
}
