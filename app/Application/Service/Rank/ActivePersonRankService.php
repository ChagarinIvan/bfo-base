<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\Rank\JuniorThirdRankChecker;
use App\Domain\Rank\PreviousCompletedRankFiller;
use App\Domain\Rank\RankRepository;

final readonly class ActivePersonRankService
{
    public function __construct(
        private RankRepository $ranks,
        private JuniorThirdRankChecker $thirdRankChecker,
        private RankAssembler $assembler,
        private PreviousCompletedRankFiller $previousCompletedRankFiller,
    ) {
    }

    public function execute(ActivePersonRank $command): ?ViewRankDto
    {
        $lastRank = $this->ranks->oneByCriteria($command->criteria());

        if ($lastRank === null) {
            $thirdJuniorRank = $this->thirdRankChecker->check($command->personId());

            if ($thirdJuniorRank) {
                $this->ranks->add($thirdJuniorRank);
            }

            $lastRank = $thirdJuniorRank;
        }

        if ($lastRank) {
            $lastRank = $this->previousCompletedRankFiller->fill($lastRank, $command->date());
        }

        return $lastRank ? $this->assembler->toViewRankDto($lastRank) : null;
    }
}
