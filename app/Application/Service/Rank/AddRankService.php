<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\RankRepository;

final readonly class AddRankService
{
    public function __construct(
        private RankFactory $factory,
        private RankRepository $ranks,
        private RankAssembler $assembler,
    ) {
    }

    public function execute(AddRank $command): ViewRankDto
    {
        $rank = $this->factory->create($command->rankInput());
        $this->ranks->add($rank);

        return $this->assembler->toViewRankDto($rank);
    }
}
