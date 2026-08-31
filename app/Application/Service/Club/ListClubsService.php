<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListClubsService
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubAssembler $assembler,
    ) {
    }

    /** @return Slice<ViewClubDto> */
    public function execute(ListClubs $command): Slice
    {
        return $this->clubs
            ->paginate($command->criteria())
            ->map($this->assembler->toViewClubDto(...))
        ;
    }
}
