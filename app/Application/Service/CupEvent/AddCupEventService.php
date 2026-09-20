<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventAlreadyExists;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Cup\CupEvent\Factory\CupEventFactory;
use App\Domain\Cup\Exception\CupNotExists;
use App\Domain\Event\Exception\EventNotExists;

final readonly class AddCupEventService
{
    public function __construct(
        private CupEventFactory $factory,
        private CupEventRepository $cupEvents,
        private CupEventAssembler $assembler
    ) {
    }

    /** @throws CupNotFound|EventNotFound|CupEventAlreadyExists */
    public function execute(AddCupEvent $command): ViewCupEventDto
    {
        try {
            $cupEvent = $this->factory->create($command->input());
        } catch (CupAlreadyContainsEvent|CupNotExists|EventNotExists $exception) {
            throw match (true) {
                $exception instanceof CupNotExists => new CupNotFound(previous: $exception),
                $exception instanceof EventNotExists => new EventNotFound(previous: $exception),
                default => new CupEventAlreadyExists(previous: $exception),
            };
        }

        $this->cupEvents->add($cupEvent);

        return $this->assembler->toViewCupEventDto($cupEvent);
    }
}
