<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\Exception\CupEventAlreadyExists;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventUpdater;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Event\Exception\EventNotExists;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateCupEventService
{
    public function __construct(private CupEventRepository $cupEvents, private CupEventUpdater $updater, private CupEventAssembler $assembler, private TransactionManager $transactional)
    {
    }

    /** @throws CupEventNotFound|EventNotFound|CupEventAlreadyExists */
    public function execute(UpdateCupEvent $command): ViewCupEventDto
    {
        return $this->transactional->run(function () use ($command): ViewCupEventDto {
            $cupEvent = $this->cupEvents->lockById($command->id()) ?? throw new CupEventNotFound();

            try {
                $this->updater->update($cupEvent, $command->input());
            } catch (CupAlreadyContainsEvent|EventNotExists $exception) {
                throw match (true) {
                    $exception instanceof EventNotExists => new EventNotFound(previous: $exception),
                    default => new CupEventAlreadyExists(previous: $exception),
                };
            }

            $this->cupEvents->update($cupEvent);

            return $this->assembler->toViewCupEventDto($cupEvent);
        });
    }
}
