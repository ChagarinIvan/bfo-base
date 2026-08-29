<?php

declare(strict_types=1);

namespace App\Application\Service\Competition;

use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListCompetitionsService
{
    public function __construct(
        private CompetitionRepository $competitions,
        private CompetitionAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewCompetitionDto> */
    public function execute(ListCompetitions $command): Slice
    {
        return $this->competitions
            ->paginate($command->criteria())
            ->map($this->assembler->toViewCompetitionDto(...))
        ;
    }
}
