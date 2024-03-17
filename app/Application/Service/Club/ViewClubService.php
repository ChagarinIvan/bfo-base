<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Domain\Club\ClubRepository;

final readonly class ViewClubService
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubAssembler $assembler,
    ) {
    }

    /** @throws ClubNotFound */
    public function execute(ViewClub $command): ViewClubDto
    {
        $club = $this->clubs->byId($command->id()) ?? throw new ClubNotFound();

        return $this->assembler->toViewClubDto($club);
    }
}
