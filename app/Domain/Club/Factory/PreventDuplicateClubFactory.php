<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

use App\Domain\Club\Club;
use App\Domain\Club\ClubInput;
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
        $criteria = new Criteria(['normalizedName' => $input->info->normalizeName]);

        if ($this->clubs->oneByCriteria($criteria) instanceof Club) {
            throw ClubAlreadyExist::byName($input->info->name);
        }

        return $this->decorated->create($input);
    }
}
