<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Domain\Cup\CupRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListCupService
{
    public function __construct(
        private CupRepository $cups,
        private CupAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewCupDto> */
    public function execute(ListCup $command): Slice
    {
        return $this->cups
            ->paginate($command->criteria())
            ->map($this->assembler->toViewCupDto(...))
        ;
    }
}
