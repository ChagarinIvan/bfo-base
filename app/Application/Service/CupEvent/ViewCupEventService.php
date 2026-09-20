<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\CupEvent\ViewCupEventDto;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Domain\Cup\CupEvent\CupEventRepository;

final readonly class ViewCupEventService
{
    public function __construct(private CupEventRepository $cupEvents, private CupEventAssembler $assembler)
    {
    }

    /** @throws CupEventNotFound */
    public function execute(ViewCupEvent $command): ViewCupEventDto
    {
        $cupEvent = $this->cupEvents->byId($command->id()) ?? throw new CupEventNotFound();

        return $this->assembler->toViewCupEventDto($cupEvent);
    }
}
