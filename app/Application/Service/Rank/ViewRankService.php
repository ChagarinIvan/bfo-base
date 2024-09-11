<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Domain\Rank\RankRepository;

final readonly class ViewRankService
{
    public function __construct(
        private RankRepository $ranks,
        private RankAssembler $assembler,
    ) {
    }

    /** @throws RankNotFound */
    public function execute(ViewRank $command): ViewRankDto
    {
        $rank = $this->ranks->byId($command->id()) ?? throw new RankNotFound;

        return $this->assembler->toViewRankDto($rank);
    }
}
