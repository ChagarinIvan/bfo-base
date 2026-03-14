<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\RankRepository;
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
