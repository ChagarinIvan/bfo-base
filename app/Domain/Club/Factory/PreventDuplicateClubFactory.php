<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Shared\Criteria;

final readonly class PreventDuplicateClubFactory implements ClubFactory
{
    public function __construct(
        private ClubFactory $decorated,
        private ClubRepository $clubs,
    ) {
    }

    public function create(ClubInput $input): Club
    {
        if ($this->clubs->oneByCriteria(new Criteria(['name' => $input->name]))) {
            throw ClubAlreadyExist::byName($input->name);
        }

        return $this->decorated->create($input);
    }
}
