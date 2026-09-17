<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckRowAssembler;
use App\Application\Dto\RankCheck\RankCheckRowDto;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListRankCheckRowsService
{
    public function __construct(
        private RankCheckRowRepository $rows,
        private RankCheckRowAssembler $assembler,
    ) {
    }

    /** @return Slice<RankCheckRowDto> */
    public function execute(ListRankCheckRows $command): Slice
    {
        return $this->rows
            ->paginate($command->criteria())
            ->map($this->assembler->toDto(...))
        ;
    }
}
