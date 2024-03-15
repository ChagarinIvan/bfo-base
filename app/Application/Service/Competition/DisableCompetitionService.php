<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class DisableCompetitionService
{
    public function __construct(
        private CompetitionRepository $competitions,
        private Clock $clock,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws CompetitionNotFound */
    public function execute(DisableCompetition $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $competition = $this->competitions->lockById($command->id()) ?? throw new CompetitionNotFound();
            $impression = new Impression($this->clock->now(), $command->userId());
            $competition->disable($impression);

            $this->competitions->update($competition);
        });
    }
}
