<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ClubOptionDto;
use App\Domain\Club\ClubRepository;

final readonly class ListAllClubService
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubAssembler $assembler,
    ) {
    }

    /** @return list<ClubOptionDto> */
    public function execute(): array
    {
        return $this->clubs
            ->all()
            ->map($this->assembler->toClubOptionDto(...))
            ->values()
            ->all()
        ;
    }
}
