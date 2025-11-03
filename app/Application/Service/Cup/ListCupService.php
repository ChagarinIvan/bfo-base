<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Domain\Cup\CupRepository;
use function array_map;

final readonly class ListCupService
{
    public function __construct(
        private CupRepository $cups,
        private CupAssembler $assembler,
    ) {
    }

    /** @return ViewCupDto[] */
    public function execute(ListCup $command): array
    {
        $cups = [];

        foreach ($this->cups->byCriteria($command->criteria())->all() as $cup) {
            $viewCup = $this->assembler->toViewCupDto($cup);
            $cups[] = $viewCup;
        }

        return $cups;
    }
}
