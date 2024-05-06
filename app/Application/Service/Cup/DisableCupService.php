<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DisableCupService
{
    public function __construct(
        private CupRepository $cups,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws CupNotFound */
    public function execute(DisableCup $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $cup = $this->cups->lockById($command->id()) ?? throw new CupNotFound();
            $impression = new Impression($this->clock->now(), $command->userId());
            $cup->disable($impression);

            $this->cups->update($cup);
        });
    }
}
