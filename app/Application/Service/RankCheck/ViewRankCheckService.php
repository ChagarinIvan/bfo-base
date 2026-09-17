<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckAssembler;
use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Domain\RankCheck\RankCheckRepository;

final readonly class ViewRankCheckService
{
    public function __construct(
        private RankCheckRepository $checks,
        private RankCheckAssembler $assembler,
    ) {
    }

    public function execute(ViewRankCheck $command): ViewRankCheckDto
    {
        $check = $this->checks->byId($command->id) ?? throw new RankCheckNotFound();

        return $this->assembler->toViewRankCheckDto($check);
    }
}
