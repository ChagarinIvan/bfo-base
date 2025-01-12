<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\Rank\RankRepository;
use function array_map;

final readonly class PersonRanksService
{
    public function __construct(
        private RankRepository $ranks,
        private RankAssembler $assembler,
    ) {
    }

    /** @return ViewRankDto[] */
    public function execute(PersonRanks $command): array
    {
        return array_map(
            $this->assembler->toViewRankDto(...),
            $this->ranks->byCriteria($command->criteria())->all()
        );
    }
}
