<?php

declare(strict_types=1);

namespace App\Application\Service\Club;

use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\Exception\FailedToAddClub;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\Factory\ClubFactory;

final readonly class AddClubService
{
    public function __construct(
        private ClubFactory $factory,
        private ClubRepository $clubs,
        private ClubAssembler $assembler,
    ) {
    }

    /** @throws FailedToAddClub */
    public function execute(AddClub $command): ViewClubDto
    {
        try {
            $club = $this->factory->create($command->clubInput());
        } catch (ClubAlreadyExist $e) {
            throw FailedToAddClub::dueError($e);
        }

        $this->clubs->add($club);

        return $this->assembler->toViewClubDto($club);
    }
}
