<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Competition\Factory\CompetitionFactory;

final readonly class AddCompetitionService
{
    public function __construct(
        private CompetitionFactory $factory,
        private CompetitionRepository $competitions,
        private CompetitionAssembler $assembler,
    ) {
    }

    public function execute(AddCompetition $command): ViewCompetitionDto
    {
        $competition = $this->factory->create($command->competitionInput());
        $this->competitions->add($competition);

        return $this->assembler->toViewCompetitionDto($competition);
    }
}
