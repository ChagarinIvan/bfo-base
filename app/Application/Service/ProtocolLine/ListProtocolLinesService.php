<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\ProtocolLine\ProtocolLineAssembler;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListProtocolLinesService
{
    public function __construct(
        private ProtocolLineRepository $lines,
        private ProtocolLineAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewProtocolLineDto> */
    public function execute(ListProtocolLines $command): Slice
    {
        return $this->lines
            ->paginate($command->criteria(), $command->resources())
            ->map($this->assembler->toViewProtocolLineDto(...))
        ;
    }
}
