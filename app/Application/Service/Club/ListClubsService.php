<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use function array_map;

final readonly class ListClubsService
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubAssembler $assembler,
    ) {
    }

    /** @return ViewClubDto[] */
    public function execute(): array
    {
        return array_map(
            $this->assembler->toViewClubDto(...),
            $this->clubs->byCriteria(Criteria::empty())->all()
        );
    }
}
