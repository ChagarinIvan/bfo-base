<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckAssembler;
use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListRankChecksService
{
    public function __construct(
        private RankCheckRepository $checks,
        private RankCheckAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewRankCheckDto> */
    public function execute(ListRankChecks $command): Slice
    {
        return $this->checks
            ->paginate($command->criteria())
            ->map($this->assembler->toViewRankCheckDto(...))
        ;
    }
}
