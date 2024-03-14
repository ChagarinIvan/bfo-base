<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Domain\Competition\CompetitionRepository;

final readonly class ViewCompetitionService
{
    public function __construct(
        private CompetitionRepository $competitions,
        private CompetitionAssembler $assembler,
    ) {
    }

    /** @throws CompetitionNotFound */
    public function execute(ViewCompetition $command): ViewCompetitionDto
    {
        $competition = $this->competitions->byId($command->id()) ?? throw new CompetitionNotFound();

        return $this->assembler->toViewCompetitionDto($competition);
    }
}
