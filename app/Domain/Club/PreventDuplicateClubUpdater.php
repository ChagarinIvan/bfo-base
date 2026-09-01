<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Shared\Criteria;

final readonly class PreventDuplicateClubUpdater implements ClubUpdater
{
    public function __construct(
        private ClubUpdater $decorated,
        private ClubRepository $clubs,
    ) {
    }

    public function update(Club $club, ClubInput $input): Club
    {
        $criteria = new Criteria([
            'normalizedName' => $input->info->normalizeName,
            'excludeId' => $club->id,
        ]);

        if ($this->clubs->oneByCriteria($criteria) instanceof Club) {
            throw ClubAlreadyExist::byName($input->info->name);
        }

        return $this->decorated->update($club, $input);
    }
}
