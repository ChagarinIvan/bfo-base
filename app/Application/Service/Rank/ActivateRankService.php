<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Rank\Factory\FinishDateCalculator;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class ActivateRankService
{
    public function __construct(
        private RankRepository $ranks,
        private RankAssembler $assembler,
        private FinishDateCalculator $calculator,
        private TransactionManager $transactional,
        private Clock $clock,
    ) {
    }

    public function execute(ActivateRank $command): ViewRankDto
    {
        return $this->transactional->run(function () use ($command): ViewRankDto {
            $rank = $this->ranks->lockById($command->id()) ?? throw new RankNotFound();
            $impression = new Impression($this->clock->now(), $command->footprint());
            $rank->activate($this->calculator, $command->startDate(), $impression);
            $this->ranks->update($rank);

            return $this->assembler->toViewRankDto($rank);
        });
    }
}
