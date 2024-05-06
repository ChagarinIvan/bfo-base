<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Domain\CupEvent\CupEventRepository;

final readonly class DisableCupEventService
{
    public function __construct(
        private CupEventRepository $cups,
    ) {
    }

    /**
     * TODO REPLACE WITH DOMAIN
     * @throws CupEventNotFound
     */
    public function execute(DisableCupEvent $command): void
    {
        $cupEvent = $this->cups->byId($command->id()) ?? throw new CupEventNotFound;
        $cupEvent->delete();
    }
}
