<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateCompetitionInfoService
{
    public function __construct(
        private CompetitionRepository $competitions,
        private Clock $clock,
        private CompetitionAssembler $assembler,
        private TransactionManager $transactional,
    ) {
    }

    /** @throws CompetitionNotFound */
    public function execute(UpdateCompetitionInfo $command): ViewCompetitionDto
    {
        /** @var ViewCompetitionDto $dto */
        $dto = $this->transactional->run(function () use ($command): ViewCompetitionDto {
            $competition = $this->competitions->lockById($command->id()) ?? throw new CompetitionNotFound();
            $impression = new Impression($this->clock->now(), $command->userId());
            $competition->updateInfo($command->info(), $impression);
            $this->competitions->update($competition);

            return $this->assembler->toViewCompetitionDto($competition);
        });

        return $dto;
    }
}
