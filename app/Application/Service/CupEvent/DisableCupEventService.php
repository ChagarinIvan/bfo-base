<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent;

use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\CupEvent\CupEventRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DisableCupEventService
{
    public function __construct(
        private CupEventRepository $cupsEvents,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /**
     * @throws CupEventNotFound
     */
    public function execute(DisableCupEvent $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $cup = $this->cupsEvents->lockById($command->id()) ?? throw new CupEventNotFound;
            $impression = new Impression($this->clock->now(), $command->userId());
            $cup->disable($impression);

            $this->cupsEvents->update($cup);
        });
    }
}
