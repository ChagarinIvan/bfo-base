<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\Rank\JuniorThirdRankChecker;
use App\Domain\Rank\PreviousCompletedRankFiller;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;

final readonly class ActivePersonRankService
{
    public function __construct(
        private RankRepository $ranks,
        private JuniorThirdRankChecker $thirdRankChecker,
        private RankAssembler $assembler,
        private PreviousCompletedRankFiller $previousCompletedRankFiller,
        private Clock $clock,
    ) {
    }

    public function execute(ActivePersonRank $command): ?ViewRankDto
    {
        $lastRank = $this->ranks->oneByCriteria($this->criteriaWithDate($command));

        if ($lastRank === null) {
            $thirdJuniorRank = $this->thirdRankChecker->check($command->personId(), $command->date());

            if ($thirdJuniorRank && $thirdJuniorRank->start_date < $command->date()) {
//                dump('add third junior rank');
                $this->ranks->add($thirdJuniorRank);
                $lastRank = $thirdJuniorRank;
            }
        }

        if (!$lastRank) {
            $lastCompletedRank = $this->ranks->oneByCriteria($this->criteriaWithoutDate($command));
            if ($lastCompletedRank) {
                $lastRank = $this->previousCompletedRankFiller->fill($lastCompletedRank, $command->date());
            }
        }

        return $lastRank ? $this->assembler->toViewRankDto($lastRank) : null;
    }

    public function criteriaWithDate(ActivePersonRank $command): Criteria
    {
        return new Criteria(['person_id' => $command->personId(), 'activated' => true, 'date' => $command->date() ?? $this->clock->now()]);
    }

    public function criteriaWithoutDate(ActivePersonRank $command): Criteria
    {
        return new Criteria(['person_id' => $command->personId(), 'activated' => true, 'startDateLess' => $command->date() ?? $this->clock->now()]);
    }
}
