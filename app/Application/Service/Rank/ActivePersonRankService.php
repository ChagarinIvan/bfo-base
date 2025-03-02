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
        $lastRank = $this->ranks->oneByCriteria($command->criteriaWithDate());

        if ($lastRank) {
            dump('Actual last rank ' . $lastRank->rank);
        }
        if ($lastRank === null) {
            $thirdJuniorRank = $this->thirdRankChecker->check($command->personId());

            if ($thirdJuniorRank) {
                $this->ranks->add($thirdJuniorRank);
            }

            $lastRank = $thirdJuniorRank;
        }

        if (!$lastRank) {
            $lastCompletedRank = $this->ranks->oneByCriteria($command->criteriaWithoutDate());
            if ($lastCompletedRank) {
                dump('Last completed rank ' . $lastCompletedRank->rank);
            }

            if ($lastCompletedRank) {
                $lastRank = $this->previousCompletedRankFiller->fill($lastCompletedRank, $command->date());
            }
        }

        return $lastRank ? $this->assembler->toViewRankDto($lastRank) : null;
    }
}
