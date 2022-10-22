<?php

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Exception\Club\ClubNotFound;
use App\Domain\Club\Repository\ClubRepository;

final class ViewClubService
{
    public function __construct(
        private readonly ClubAssembler $assembler,
        private readonly ClubRepository $clubs,
    ) {}

    public function execute(ViewClub $command): ViewClubDto
    {
        $clubId = $command->clubId();

        $club = $this->clubs->byId($clubId);
        if (!$club) {
            throw ClubNotFound::byId($clubId);
        }

        return $this->assembler->toViewCluDto($club);
    }
}
