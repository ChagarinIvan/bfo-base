<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Services\RankService;

final readonly class RefillPersonRanksService
{
    public function __construct(
        private RankService $rankService,
    ) {
    }

    public function execute(RefillPersonRanks $command): void
    {
        $this->rankService->reFillRanksByPersonId($command->personId());
    }
}
