<?php

declare(strict_types=1);

namespace App\Application\Service\Distance;

use App\Application\Dto\Distance\DistanceAssembler;
use App\Application\Dto\Distance\ViewDistanceDto;
use App\Domain\Distance\DistanceRepository;

final readonly class ListEventDistancesService
{
    public function __construct(
        private DistanceRepository $distances,
        private DistanceAssembler $assembler,
    ) {
    }

    /** @return list<ViewDistanceDto> */
    public function execute(ListEventDistances $command): array
    {
        return $this->distances
            ->byCriteria($command->criteria())
            ->map($this->assembler->toViewDistanceDto(...))
            ->all()
        ;
    }
}
