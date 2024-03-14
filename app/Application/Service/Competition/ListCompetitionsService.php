<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Domain\Competition\CompetitionRepository;
use function array_map;

final readonly class ListCompetitionsService
{
    public function __construct(
        private CompetitionRepository $competitions,
        private CompetitionAssembler $assembler,
    ) {
    }

    /** @return ViewCompetitionDto[] */
    public function execute(ListCompetitions $command): array
    {
        return array_map(
            $this->assembler->toViewCompetitionDto(...),
            $this->competitions->byCriteria($command->criteria())->all()
        );
    }
}
