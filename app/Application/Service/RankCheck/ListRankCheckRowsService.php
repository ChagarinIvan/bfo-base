<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckRowAssembler;
use App\Application\Dto\RankCheck\RankCheckRowDto;
use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListRankCheckRowsService
{
    public function __construct(
        private RankCheckRepository $checks,
        private RankCheckRowRepository $rows,
        private RankCheckRowAssembler $assembler,
    ) {
    }

    /** @return Slice<RankCheckRowDto> */
    public function execute(ListRankCheckRows $command): Slice
    {
        $this->checks->byId($command->rankCheckId) ?? throw new RankCheckNotFound();

        return $this->rows
            ->paginateRows(new Criteria(['rankCheckId' => $command->rankCheckId]))
            ->map($this->assembler->toDto(...))
        ;
    }
}
