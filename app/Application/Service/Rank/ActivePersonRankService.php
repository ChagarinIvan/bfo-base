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
//        dump('$lastRank?->rank: '. $lastRank?->rank);

        if ($lastRank === null) {
            $lastCompletedRank = $this->ranks->oneByCriteria($this->criteriaWithoutDate($command));
//            dump('$lastCompletedRank: ' . $lastCompletedRank?->rank ?? '---');
            if ($lastCompletedRank) {
                $lastRank = $this->previousCompletedRankFiller->fill($lastCompletedRank, $command->date());
                dump('before while');

                while ($lastRank !== null && $lastRank->finish_date->lessThan($this->clock->now())) {
                    dump('$lastRank !== null' . $lastRank !== null);
                    dump('$lastRank->finish_date->lessThan($this->clock->now())' . $lastRank->finish_date->lessThan($this->clock->now()));
                    dump('while $lastRank->id' . $lastRank->id);
                    dump('while $lastRank->rank' . $lastRank->rank);
                    dump('while $lastRank->start_date' . $lastRank->start_date->format('Y-m-d'));
                    dump('while $lastRank->finish_date' . $lastRank->finish_date->format('Y-m-d'));
                    dump('while $lastRank->activated_date' . $lastRank->activated_date->format('Y-m-d'));
                    $lastRank = $this->previousCompletedRankFiller->fill($lastRank, $command->date());
                }
            }
        }

        if ($lastRank === null) {
            $thirdJuniorRank = $this->thirdRankChecker->check($command->personId(), $command->date());
            if ($thirdJuniorRank && (($command->date() === null) || ($thirdJuniorRank->start_date < $command->date()))) {
//                dump('add third junior rank');
                $this->ranks->add($thirdJuniorRank);
                $lastRank = $thirdJuniorRank;
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
